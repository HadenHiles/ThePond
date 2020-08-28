<?php

if (class_exists('MeprUser')) {
    class MiddleManClass extends MeprUser { }
} else {
    class MiddleManClass { }
}
class HthMeprUser extends MiddleManClass {
    /**
     * @return array of membership ID's
     */
    public function get_memberships( $user_id = false ){
    
        if( class_exists('MeprUser') ) {
            
            if( ! $user_id ){
                $user_id = get_current_user_id();
            }
            
            $user            = new MeprUser( $user_id );
            $get_memberships = $user->active_product_subscriptions();
            
            if( !empty( $get_memberships ) ){
                $user_memberships = array_values( array_unique( $get_memberships ) );
            } else {
                $user_memberships = array();
            }
            
            return $user_memberships;
            
        } else {
            return false;
        }
    }

    /**
     * @return array of membership ID's
     */
    public function get_subscriptions( $user_id = false ){
    
        if( class_exists('MeprUser') ){
            
            if( ! $user_id ){
                $user_id = get_current_user_id();
            }
            
            $user            = new MeprUser( $user_id );
            $user_subscriptions = $user->active_product_subscriptions('transactions');
            
            return $user_subscriptions;
            
        } else {
            return false;
        }
    }
}

?>