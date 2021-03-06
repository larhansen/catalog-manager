<?php

namespace CatalogManager;

class Text {

    
    public static function generate( $arrDCAField, $arrField, $objModule = null, $blnActive = true ) {

        $arrDCAField['eval']['readonly'] = Toolkit::getBooleanByValue( $arrField['readonly'] );

        if ( $arrField['rgxp'] ) {

            $arrDCAField['eval']['rgxp'] = $arrField['rgxp'];
        }

        if ( $arrField['minlength'] ) {

            $arrDCAField['eval']['minlength'] = intval( $arrField['minlength'] );
        }

        if ( $arrField['maxlength'] ) {

            $arrDCAField['eval']['maxlength'] = intval( $arrField['maxlength'] );
        }

        if ( $arrField['pagePicker'] ) {

            $arrDCAField['eval']['rgxp'] = 'url';
            $arrDCAField['eval']['decodeEntities'] = true;
            $arrDCAField['eval']['tl_class'] .= ' wizard';
            $arrDCAField['wizard'][] = [ 'CatalogManager\DcCallbacks', 'pagePicker' ];
        }

        if ( $arrField['autoCompletionType'] ) {

            $arrDCAField['eval']['tl_class'] .= ' ctlg_awesomplete';
            $arrDCAField['eval']['tl_class'] .= ( $arrField['multiple'] ? ' multiple' : '' );
            $arrDCAField['eval']['tl_class'] .= ( version_compare(VERSION, '4.0', '>=') ? ' _contao4' : ' _contao3' );

            if ( \Input::get( 'ctlg_autocomplete_query' ) && \Input::get('ctlg_fieldname') == $arrField['fieldname'] && $blnActive ) {

                $strModuleID = !is_null( $objModule ) && is_object( $objModule ) ? $objModule->id : '';

                static::sendJsonResponse( $arrField, $strModuleID, \Input::get( 'ctlg_autocomplete_query' ) );
            }

            $objScriptLoader = new CatalogScriptLoader();
            $objScriptLoader->loadScript('awesomplete-backend', 'TL_JAVASCRIPT' );
            $objScriptLoader->loadStyle('awesomplete', 'TL_CSS' );
        }

        return $arrDCAField;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog ) {

        $varValue = deserialize( $varValue );
        
        if ( Toolkit::isEmpty( $varValue ) && is_string( $varValue ) ) return '';
        if ( is_array( $varValue ) && empty( $varValue ) ) return [];

        if ( is_array( $varValue ) || $arrField['multiple'] ) {

            $arrReturn = [];
            $varValue = Toolkit::parseMultipleOptions( $varValue );

            if ( !empty( $varValue ) && is_array( $varValue ) ) {

                foreach ( $varValue as $strValue ) {

                    $arrReturn[ $strValue ] = $strValue;
                }
            }

            return $arrReturn;
        }

        return $varValue;
    }


    protected static function sendJsonResponse( $arrField, $strModuleID, $strKeyword ) {

        $arrField['optionsType'] = $arrField['autoCompletionType'];
        $arrField['dbTableValue'] = $arrField['dbTableKey'];

        $objOptionGetter = new OptionsGetter( $arrField, $strModuleID, [ $strKeyword ] );
        $arrWords = array_values( $objOptionGetter->getOptions() );

        header('Content-Type: application/json');

        echo json_encode( [

            'word' => $strKeyword,
            'words' => $arrWords

        ], 12 );

        exit;
    }
}