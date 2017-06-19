<?php

namespace CatalogManager;

class tl_catalog_form extends \Backend {


    public function checkPermission() {

        $objDCAPermission = new DCAPermission();
        $objDCAPermission->checkPermission( 'tl_catalog_form' , 'filterform', 'filterformp' );
    }


    public function getCatalogs() {
        
        return is_array( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] ) ? array_keys( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] ) : [];
    }
}