<!DOCTYPE html>
<html lang="en">
	<head>
		<title>An upgrade has been started</title>
		<style type='text/css'>
			body {
				background: #f9f9f9;
				margin: 0;
				padding: 30px 20px;
				font-family: "Helvetica Neue", helvetica, arial, sans-serif;
			}

			#error {
				max-width: 800px;
				background: #fff;
				margin: 0 auto;
			}

			h1 {
				background: #151515;
				color: #fff;
				font-size: 22px;
				font-weight: 500;
				padding: 10px;
			}

				h1 span {
					color: #7a7a7a;
					font-size: 14px;
					font-weight: normal;
				}

			#content {
				padding: 20px;
				line-height: 1.6;
			}

			.buttonsRow {
				text-align: center;
				margin-top: 50px;
			}

			.button {
				background: #151515;
				color: #fff;
				border: 0;
				line-height: 34px;
				padding: 0 15px;
				font-family: "Helvetica Neue", helvetica, arial, sans-serif;
				font-size: 14px;
				border-radius: 3px;
				cursor: pointer;
			}

			.nodontdoit {
				background: #e0e0e0;
				color: #222;
				margin-left: 50px;
			}
		</style>
	</head>
	<body>
		<div id='error'>
			<h1>You have started an upgrade</h1>
			<div id='content'>
				A new version has been uploaded and the upgrade process has been started. You must complete the upgrade before you will be able to access the AdminCP.
				
				<div class='buttonsRow'>
					<button onclick="window.location='upgrade/'" class='button'>Complete Upgrade</button>
					<?php if( \IPS\RECOVERY_MODE === TRUE ): ?><button onclick="window.location=window.location + '&noWarning=1'" class='button nodontdoit'>Continue to AdminCP</button><?php endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>