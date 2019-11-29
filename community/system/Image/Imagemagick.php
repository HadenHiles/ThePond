<?php
/**
 * @brief		Image Class - ImageMagick
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @since		06 Mar 2014
 */

namespace IPS\Image;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Image Class - ImageMagick
 */
class _Imagemagick extends \IPS\Image
{
	/**
	 * @brief	Temporary filename
	 */
	protected $tempFile = NULL;
	
	/**
	 * @brief	Imagick object
	 */
	protected $imagick;
	
	/**
	 * Constructor
	 *
	 * @param	string|NULL	$contents	Contents
	 * @param	bool		$noImage	We are creating a new instance of the object internally and are not passing an image string
	 * @return	void
	 * @throws	\InvalidArgumentException
	 */
	public function __construct( $contents, $noImage=FALSE )
	{
		/* If we are just creating an instance of the object without passing image contents as a string, return now */
		if( $noImage === TRUE )
		{
			return;
		}

		$this->tempFile = tempnam( \IPS\TEMP_DIRECTORY, 'imagick' );
		\file_put_contents( $this->tempFile, $contents );
		
		try
		{
			$this->imagick = new \Imagick( $this->tempFile );

			/* Set quality (if image format is JPEG) */
			if ( \in_array( mb_strtolower( $this->imagick->getImageFormat() ), array( 'jpg', 'jpeg' ) ) )
			{
				$this->imagick->setImageCompressionQuality( (int) \IPS\Settings::i()->image_jpg_quality ?: 85 );
			}
		}
		catch ( \ImagickException $e )
		{
			throw new \InvalidArgumentException( $e->getMessage(), $e->getCode() );
		}

		/* Set width/height */
		$this->setDimensions();
	}
	
	/**
	 * Destructor
	 *
	 * @return	void
	 */
	public function __destruct()
	{
		if( $this->tempFile !== NULL )
		{
			unlink( $this->tempFile );
		}
	}
	
	/**
	 * Get Contents
	 *
	 * @return	string
	 */
	public function __toString()
	{
		/* If possible, retain the color profiles when stripping EXIF data */
		if( \IPS\Settings::i()->imagick_strip_exif )
		{
			$imageColorProfiles	= array();

			try
			{
				$imageColorProfiles = $this->imagick->getImageProfiles( 'icc', true );
			}
			catch( \ImagickException $e ){}

			$this->imagick->stripImage();

			if( \count( $imageColorProfiles ) )
			{
				foreach( $imageColorProfiles as $type => $profile )
				{
					try
					{
						$this->imagick->profileImage( $type, $profile );
					}
					catch( \ImagickException $e ){}
				}
			}
		}

		return (string) $this->imagick->getImagesBlob();
	}
	
	/**
	 * Resize
	 *
	 * @param	int		$width			Width (in pixels)
	 * @param	int		$height			Height (in pixels)
	 * @return	void
	 */
	public function resize( $width, $height )
	{
		$format = $this->imagick->getImageFormat();

		if( mb_strtolower( $format ) == 'gif' )
		{
			$this->imagick	= $this->imagick->coalesceImages();

			foreach( $this->imagick as $frame )
			{
				$frame->thumbnailImage( $width, $height );
			}

			/* Needs ImageMagick 6.2.9 or higher for optimizeImageLayers */
			try
			{
				$this->imagick	= $this->imagick->optimizeImageLayers();
			}
			catch( \ImagickException $e )
			{
				$this->imagick	= $this->imagick->deconstructImages();
			}
		}
		else
		{
			$this->imagick->thumbnailImage( $width, $height );
		}

		/* Set width/height */
		$this->setDimensions();
	}

	/**
	 * Crop to a given width and height (will attempt to downsize first)
	 *
	 * @param	int		$width			Width (in pixels)
	 * @param	int		$height			Height (in pixels)
	 * @return	void
	 */
	public function crop( $width, $height )
	{
		$this->imagick->cropThumbnailImage( $width, $height );

		/* Set width/height */
		$this->setDimensions();
	}
	
	/**
	 * Crop at specific points
	 *
	 * @param	int		$point1X		x-point for top-left corner
	 * @param	int		$point1Y		y-point for top-left corner
	 * @param	int		$point2X		x-point for bottom-right corner
	 * @param	int		$point2Y		y-point for bottom-right corner
	 * @return	void
	 */
	public function cropToPoints( $point1X, $point1Y, $point2X, $point2Y )
	{
		if( mb_strtolower( $this->imagick->getImageFormat() ) === 'gif' )
		{
			$this->imagick	= $this->imagick->coalesceImages();
			
			foreach( $this->imagick as $frame )
			{
				$frame->cropImage( $point2X - $point1X, $point2Y - $point1Y, $point1X, $point1Y );
				$frame->setImagePage($point2X - $point1X, $point2Y - $point1Y, 0, 0);
			}
			
			/* Needs ImageMagick 6.2.9 or higher for optimizeImageLayers */
			try
			{
				$this->imagick	= $this->imagick->optimizeImageLayers();
			}
			catch( \ImagickException $e )
			{
				$this->imagick	= $this->imagick->deconstructImages();
			}
		}
		else
		{
			$this->imagick->cropImage( $point2X - $point1X, $point2Y - $point1Y, $point1X, $point1Y );
		}

		/* Set width/height */
		$this->setDimensions();
	}
	
	/**
	 * Impose image
	 *
	 * @param	\IPS\Image	$image	Image to impose
	 * @param	int			$x		Location to impose to, x axis
	 * @param	int			$y		Location to impose to, y axis
	 * @return	void
	 */
	public function impose( $image, $x=0, $y=0 )
	{
		$this->imagick->compositeImage( $image->imagick, \Imagick::COMPOSITE_DEFAULT, $x, $y );
	}

	/**
	 * Rotate image
	 *
	 * @param	int		$angle	Angle of rotation
	 * @return	void
	 */
	public function rotate( $angle )
	{
		$this->imagick->rotateImage( new \ImagickPixel('#00000000'), $angle );

		/* Set width/height */
		$this->setDimensions();
	}

	/**
	 * Set the image width and height
	 *
	 * @return	void
	 */
	protected function setDimensions()
	{
		/* If this is a gif, we need to coalesce the image in order to get the proper dimensions */
		if ( mb_strtolower( $this->imagick->getImageFormat() ) === 'gif' )
		{
			$this->imagick = $this->imagick->coalesceImages();
		}
		
		/* Set width/height */
		$this->width = $this->imagick->getImageWidth();
		$this->height = $this->imagick->getImageHeight();
	}
	
	/**
	 * Get Image Orientation
	 *
	 * @return	int|NULL
	 */
	public function getImageOrientation()
	{
		try
		{
			/* This method does not exist in ImageMagick < 6.6.4 */
			return ( method_exists( $this->imagick, 'getImageOrientation' ) ) ? $this->imagick->getImageOrientation() : NULL;
		}
		catch( \ImagickException $e )
		{
			return NULL;
		}
	}

	/**
	 * Set image orientation
	 *
	 * @param	int		$orientation The orientation
	 * @return	void
	 */
	public function setImageOrientation( $orientation )
	{
		if( method_exists( $this->imagick, 'getImageOrientation' ) )
		{
			$this->imagick->setImageOrientation($orientation);
		}
	}

	/**
	 * Can we write text reliably on an image?
	 *
	 * @return	bool
	 */
	public static function canWriteText()
	{
		return TRUE;
	}

	/**
	 * Create a new blank canvas image
	 *
	 * @param	int		$width	Width
	 * @param	int		$height	Height
	 * @param	array 	$rgb	Color to use for bg
	 * @return	\IPS\Image
	 */
	public static function newImageCanvas( $width, $height, $rgb )
	{
		$obj			= new static( NULL, TRUE );
		$obj->imagick	= new \Imagick();
		$obj->width		= $width;
		$obj->height	= $height;
		$obj->type		= 'png';
		$pixel			= new \ImagickPixel( "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, 1)" );

		$obj->imagick->newImage( $width, $height, $pixel );
		$obj->imagick->setImageFormat( "png" );

		return $obj;
	}

	/**
	 * Write text on our image
	 *
	 * @param	string	$text	Text
	 * @param	string	$font	Path to font to use
	 * @param	int		$size	Size of text
	 * @return	void
	 */
	public function write( $text, $font, $size )
	{
		$draw			= new \ImagickDraw();
		$draw->setTextAntialias( true );
		$draw->setGravity( \Imagick::GRAVITY_CENTER );
		$draw->setFont( $font );
		$draw->setFontSize( $size );

		$draw->setFillColor( "rgb( 255, 255, 255 )" );

		$this->imagick->annotateImage( $draw, 0, 0, 0, $text );
	}
}