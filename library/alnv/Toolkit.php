<?php

namespace CatalogManager;

class Toolkit {


    public static $arrDateRgxp = [ 'date', 'time', 'datim' ];
    public static $arrRequireSortingModes = [ '4', '5', '6' ];
    public static $arrOperators = [ 'cut', 'copy', 'invisible' ];
    public static $arrDoNotRenderInFastMode = [ 'upload', 'map' ];
    public static $arrDigitRgxp = [ 'digit', 'natural', 'prcnt' ];
    public static $nullableOperators = [ 'isNotEmpty', 'isEmpty' ];
    public static $arrModeTypes = [ '0', '1', '2', '3', '4', '5', '6' ];
    public static $arrArrayOptions = [ 'origin', 'catalogFields', 'catalogEntityFields' ];
    public static $arrSocialSharingButtons = [ 'mail', 'twitter', 'facebook', 'xing', 'linkedin' ];
    public static $arrImageExtensions = [ 'jpg', 'jpeg', 'gif', 'png', 'svg', 'svgz', 'bmp', 'tiff', 'tif' ];
    public static $arrFileExtensions = [ 'odt', 'ods', 'odp', 'odg', 'ott', 'ots', 'otp', 'otg', 'pdf', 'doc', 'docx', 'dot', 'dotx', 'xls', 'xlsx','xlt', 'xltx', 'ppt', 'pptx', 'pot', 'potx', 'mp3', 'mp4', 'm4a','m4v','webm','ogg','ogv', 'wma', 'wmv', 'ram', 'rm', 'mov', 'zip', 'rar', '7z' ];
    public static $arrSqlTypes = [

        'c256' => "varchar(256) NOT NULL default ''",
        'c1' => "char(1) NOT NULL default ''",
        'c16' => "varchar(16) NOT NULL default ''",
        'c32' => "varchar(32) NOT NULL default ''",
        'c64' => "varchar(64) NOT NULL default ''",
        'c128' => "varchar(128) NOT NULL default ''",
        'c512' => "varchar(512) NOT NULL default ''",
        'c1024' => "varchar(1024) NOT NULL default ''",
        'c2048' => "varchar(2048) NOT NULL default ''",
        'i5' => "smallint(5) unsigned NOT NULL default '0'",
        'i10' => "int(10) unsigned NOT NULL default '0'",
        'text' => "text NULL",
        'blob' => "blob NULL",
        'binary' => "binary(16) NULL"
    ];

    public static $arrFormTemplates = [

        'checkbox' => 'form_checkbox',
        'textarea' => 'form_textarea',
        'number' => 'form_textfield',
        'text' => 'form_textfield',
        'date' => 'form_textfield',
        'select' => 'form_select',
        'upload' => 'form_upload',
        'hidden' => 'form_hidden',
        'radio' => 'form_radio'
    ];

    public static function invisiblePaletteFields() {

        return [

            'invisible',
            'start',
            'stop'
        ];
    }


    public static function columnOnlyFields() {

        return [

            'dbColumn'
        ];
    }


    public static function readOnlyFields() {

        return [

            'message'
        ];
    }


    public static function wrapperFields() {

        return [

            'fieldsetStart',
            'fieldsetStop'
        ];
    }


    public static function excludeFromDc() {

        return [

            'map',
            'fieldsetStop',
            'fieldsetStart'
        ];
    }


    public static function setDcConformInputType( $strType ) {

        $arrInputTypes = [

            'text' => 'text',
            'date' => 'text',
            'number' => 'text',
            'hidden' => 'text',
            'radio' => 'radio',
            'select' => 'select',
            'upload' => 'fileTree',
            'textarea' => 'textarea',
            'checkbox' => 'checkbox',
            'message' => 'catalogMessageWidget'
        ];

        return $arrInputTypes[ $strType ] ?: '';
    }


    public static function convertCatalogTypeToFormType( $strType ) {

        $arrFormTypes = [

            'radio' => 'radio',
            'map' => 'textfield',
            'select' => 'select',
            'upload' => 'upload',
            'text' => 'textfield',
            'date' => 'textfield',
            'number' => 'textfield',
            'hidden' => 'textfield',
            'textarea' => 'textarea',
            'checkbox' => 'checkbox',
            'message' => 'catalogMessageWidget'
        ];

        return $arrFormTypes[ $strType ] ?: '';
    }


    public static function setCatalogConformInputType( $strField ) {

        $strType = $strField['inputType'];

        if ( Toolkit::isEmpty( $strType ) ) return '';

        if ( !Toolkit::isEmpty( $strField['eval']['rgxp'] ) ) {

            if ( in_array( $strField['eval']['rgxp'], self::$arrDateRgxp ) ) return 'date';
        }

        $arrInputTypes = [

            'text' => 'text',
            'radio' => 'radio',
            'select' => 'select',
            'password' => 'text',
            'fileTree' => 'upload',
            'textarea' => 'textarea',
            'checkbox' => 'checkbox',
        ];
        
        return $arrInputTypes[ $strType ] ?: 'text';
    }
    
    
    public static function getSqlDataType( $strType ) {

        return self::$arrSqlTypes[ $strType ] ? self::$arrSqlTypes[ $strType ] : "varchar(256) NOT NULL default ''";
    }
    

    public static function isDcConformField( $arrField ) {

        if ( empty( $arrField ) && !is_array( $arrField ) ) return false;
        if ( in_array( $arrField['type'], self::excludeFromDc() ) ) return false;

        return true;
    }


    public static function columnsBlacklist() {

        return [

            'id',
            'pid',
            'tstamp',
            'origin',
            'sorting',
            'invisible',
        ];
    }


    public static function customizeAbleFields() {

        return [

            'title',
            'alias',
            'start',
            'stop'
        ];
    }


    public static function parseCatalog( $arrCatalog ) {

        $arrCatalog['cTables'] = self::parseStringToArray( $arrCatalog['cTables'] );
        $arrCatalog['operations'] = self::parseStringToArray( $arrCatalog['operations'] );
        $arrCatalog['panelLayout'] = self::parseStringToArray( $arrCatalog['panelLayout'] );
        $arrCatalog['labelFields'] = self::parseStringToArray( $arrCatalog['labelFields'] );
        $arrCatalog['headerFields'] = self::parseStringToArray( $arrCatalog['headerFields'] );
        $arrCatalog['sortingFields'] = self::parseStringToArray( $arrCatalog['sortingFields'] );
        
        return $arrCatalog;
    }

    
    public static function parseStringToArray( $strValue ) {

        if ( $strValue && is_string( $strValue ) ) {

            return deserialize( $strValue );
        }

        if ( is_array( $strValue ) ) {

            return $strValue;
        }

        return [];
    }


    public static function removeBreakLines( $strValue ) {

        if ( !$strValue || !is_string( $strValue ) ) {

            return $strValue;
        }

        return preg_replace( "/\r|\n/", "", $strValue );
    }


    public static function removeApostrophe( $strValue ) {

        if ( !$strValue || !is_string( $strValue ) ) {

            return $strValue;
        }

        return str_replace( "'", "", $strValue );
    }


    public static function parseConformSQLValue( $varValue ) {

        return str_replace( '-', '_', $varValue );
    }


    public static function isAssoc( $arrAssoc ) {

        if ( !is_array( $arrAssoc ) ) return false;

        $arrKeys = array_keys( $arrAssoc );

        return array_keys( $arrKeys ) !== $arrKeys;
    }
    

    public static function prepareValues4Db( $arrValues ) {

        $arrReturn = [];

        if ( !empty( $arrValues ) && is_array( $arrValues ) ) {

            foreach ( $arrValues as $strKey => $varValue ) {

                $arrReturn[ $strKey ] = self::prepareValue4Db( $varValue );
            }
        }

        return $arrReturn;
    }


    public static function prepareValue4Db( $varValue ) {

        if ( !self::isDefined( $varValue ) ) return $varValue;

        if ( is_array( $varValue ) ) return implode( ',', $varValue );

        if ( is_float( $varValue ) ) return floatval( $varValue );

        return $varValue;
    }


    public static function prepareValueForQuery( $varValue ) {

        if ( !empty( $varValue ) && is_array( $varValue ) ) {

            $arrReturn = [];

            $varValue = array_filter( $varValue, function ( $varValue ) {

                if ( $varValue === '0' ) return true;

                return $varValue;
            });

            foreach ( $varValue as $strKey => $strValue ) {

                $arrReturn[ $strKey ] = Toolkit::prepareValueForQuery( $strValue );
            }

            return $arrReturn;
        }

        if ( is_float( $varValue ) ) return floatval( $varValue );
        if ( preg_match('/^[1-9]/', $varValue ) ) return floatval( $varValue );
        if ( is_string( $varValue ) && $varValue === '0' ) return 0;
        if ( is_null( $varValue ) ) return '';

        return $varValue;
    }


    public static function deserialize( $strValue ) {

        $strValue = deserialize( $strValue );

        if ( !is_array( $strValue ) ) {

            return is_string( $strValue ) ? [ $strValue ] : [];
        }

        return $strValue;
    }


    public static function getBooleanByValue( $varValue ) {

        if ( !$varValue ) {

            return false;
        }

        return true;
    }


    public static function deserializeAndImplode( $strValue, $strDelimiter = ',' ) {

        if ( !$strValue || !is_string( $strValue ) ) {

            return '';
        }

        $arrValue = deserialize( $strValue );

        if ( !empty( $arrValue ) && is_array( $arrValue ) ) {

            return implode( $strDelimiter, $arrValue );
        }

        return '';
    }


    public static function isDefined( $varValue ) {

        if ( is_numeric( $varValue ) ) {

            return true;
        }

        if ( is_array( $varValue )) {

            return true;
        }

        if ( $varValue && is_string( $varValue ) ) {

            return true;
        }

        return false;
    }


    public static function parseColumns( $arrColumns ) {

        $arrReturn = [];

        if ( !empty( $arrColumns ) && is_array( $arrColumns ) ) {

            foreach ( $arrColumns as $arrColumn ) {

                if ( $arrColumn['name'] == 'PRIMARY' || $arrColumn['type'] == 'index' ) {

                    continue;
                }

                $arrReturn[ $arrColumn['name'] ] = $arrColumn['name'];
            }
        }

        return $arrReturn;
    }


    public static function parseQueries( $arrQueries, $fnCallback = null ) {

        $arrReturn = [];

        if ( !empty( $arrQueries ) && is_array( $arrQueries ) ) {

            foreach ( $arrQueries as $arrQuery ) {

                if ( in_array( $arrQuery['operator'], self::$nullableOperators ) ) $arrQuery['allowEmptyValues'] = '1';

                if ( !is_null( $fnCallback ) && is_callable( $fnCallback ) )  $arrQuery = $fnCallback( $arrQuery );

                $arrQuery = self::parseQuery( $arrQuery );

                if ( is_null( $arrQuery ) ) continue;

                if ( !empty( $arrQuery['subQueries'] ) && is_array( $arrQuery['subQueries'] ) ) {

                    $arrSubQueries = self::parseQueries( $arrQuery['subQueries'] );

                    array_insert( $arrSubQueries, 0, [[

                        'field' => $arrQuery['field'],
                        'value' => $arrQuery['value'],
                        'operator' => $arrQuery['operator'],
                    ]]);

                    $arrReturn[] = $arrSubQueries;
                }

                else {

                    $arrReturn[] = $arrQuery;
                }
            }
        }

        return $arrReturn;
    }


    public static function isEmpty( $varValue ) {

        if ( is_null( $varValue ) || $varValue === '' ) return true;

        return false;
    }


    public static function parseQuery( $arrQuery ) {

        $blnAllowEmptyValue = $arrQuery['allowEmptyValues'] ? true : false;

        if ( is_array( $arrQuery['value'] ) ) {

            if ( !empty( $arrQuery['value'] ) ) {

                foreach ( $arrQuery['value'] as $strK => $strV ) {

                    $arrQuery['value'][ $strK ] = \Controller::replaceInsertTags( $strV );
                }
            }

            if ( $arrQuery['operator'] == 'between' && is_array( $arrQuery['value'] ) ) {

                if ( $arrQuery['value'][0] === '' && $arrQuery['value'][1] === '' ) return null;

                if ( $arrQuery['value'][0] === '' ) $arrQuery['value'][0] = '0';

                if ( $arrQuery['value'][1] === '' ) {

                    $numOff = (float) $arrQuery['value'][0];
                    $arrQuery['value'][1] = $numOff * $numOff;
                }
            }
        }

        if ( is_string( $arrQuery['value'] ) ) {

            $arrQuery['value'] = \Controller::replaceInsertTags( $arrQuery['value'] );

            if ( strpos( $arrQuery['value'], ',' ) ) {

                $arrQuery['value'] = explode( ',' , $arrQuery['value'] );
            }

            if ( is_string( $arrQuery['value'] ) && $arrQuery['operator'] == 'regexp' ) {

                if ( strpos( $arrQuery['value'], ' ' ) ) {

                    $arrQuery['value'] = explode( ' ' , $arrQuery['value'] );
                }
            }
        }

        if ( is_array( $arrQuery['value'] ) && !in_array( $arrQuery['operator'], [ 'contain', 'between' ] ) ) {

            $arrQuery['multiple'] = true;
        }

        if ( ( is_null( $arrQuery['value'] ) || $arrQuery['value'] === '' ) && !$blnAllowEmptyValue ) {

            return null;
        }

        $arrQuery['value'] = self::prepareValueForQuery( $arrQuery['value'] );

        return $arrQuery;
    }


    public static function returnOnlyExistedItems( $arrItems, $arrExistedFields, $blnKeysOnly = false ) {

        $arrReturn = [];
        $arrExistedValues = $blnKeysOnly ? array_keys( $arrExistedFields ) : $arrExistedFields;

        if ( !empty( $arrItems ) && is_array( $arrItems ) ) {

            foreach ( $arrItems as $varValue ) {

                if ( !$varValue || !is_string( $varValue ) ) continue;

                if ( !in_array( $varValue, $arrExistedValues ) ) continue;

                $arrReturn[] = $varValue;
            }
        }

        return $arrReturn;
    }


    public static function getRoutingParameter( $strRoutingParameter, $blnEmptyArray = false ) {

        $arrReturn = [];
        $arrRoutingFragments = explode( '/' , $strRoutingParameter );
        
        if ( !empty( $arrRoutingFragments ) && is_array( $arrRoutingFragments ) ) {

            foreach ( $arrRoutingFragments as $strRoutingFragment ) {

                if ( !$strRoutingFragment ) continue;

                preg_match_all( '/{(.*?)}/', $strRoutingFragment, $arrMatches );

                $strParamName = implode( '', $arrMatches[1] );

                if ( $strParamName ) {

                    $arrReturn[ $strParamName ] = $blnEmptyArray ? [] : $strParamName;
                }
            }
        }

        return $arrReturn;
    }


    public static function parseMultipleOptions( $varValue ) {

        if ( is_string( $varValue ) ) {

            $varValue = explode( ',', $varValue );
        }

        return $varValue;
    }


    public static function isCoreTable( $strTable ) {

        return is_string( $strTable ) && substr( $strTable, 0, 3 ) == 'tl_';
    }


    public static function getColumnsFromCoreTable( $strTable, $blnFullContext = false ) {

        $arrReturn = [];

        \System::loadLanguageFile( $strTable );
        \Controller::loadDataContainer( $strTable );

        $arrFields = $GLOBALS['TL_DCA'][$strTable]['fields'];

        if ( !empty( $arrFields ) && is_array( $arrFields ) ) {

            foreach ( $arrFields as $strFieldname => $arrField ) {

                if ( !isset( $arrField['sql'] ) ) continue;

                $varContext = $arrField;

                if ( !$blnFullContext ) {

                    $strTitle = $strFieldname;

                    if ( is_array( $arrField['label'] ) ) {

                        $varContext = $arrField['label'][0] ? $arrField['label'][0] : $strTitle;
                    }
                }

                $arrReturn[ $strFieldname ] = $varContext;
            }
        }

        return $arrReturn;
    }


    public static function parseCatalogValues( $arrData, $arrFields = [], $blnJustStrings = false ) {

        if ( !empty( $arrData ) && is_array( $arrData ) ) {

            foreach ( $arrData as $strFieldname => $varDBValue ) {

                $varValue = null;

                if ( Toolkit::isEmpty( $varDBValue ) ) continue;

                $arrField = $arrFields[ $strFieldname ];

                if ( is_null( $arrField ) ) continue;
                if ( !$arrField['type'] ) continue;

                switch ( $arrField['type'] ) {

                    case 'upload':

                        if ( TL_MODE == 'FE' ) {

                            $varValue = Upload::parseValue( $varDBValue, $arrField, $arrData );

                            if ( is_array( $varValue ) && $arrField['fileType'] == 'gallery' ) {

                                if ( $varValue['preview'] ) $arrData[ $strFieldname . 'Preview' ] = $varValue['preview'];

                                $varValue = $varValue['gallery'];
                            }
                        }

                        else {

                            $varValue = Upload::parseThumbnails( $varDBValue, $arrField, $arrData );
                        }

                        break;

                    case 'select':

                        $varValue = Select::parseValue( $varDBValue, $arrField, $arrData );

                        break;

                    case 'checkbox':

                        $varValue = Checkbox::parseValue( $varDBValue, $arrField, $arrData );

                        break;

                    case 'radio':

                        $varValue = Radio::parseValue( $varDBValue, $arrField, $arrData);

                        break;

                    case 'date':

                        $varValue = DateInput::parseValue( $varDBValue, $arrField, $arrData );

                        break;

                    case 'number':

                        $varValue = Number::parseValue( $varDBValue, $arrField, $arrData );

                        break;

                    case 'textarea':

                        $varValue = Textarea::parseValue( $varDBValue, $arrField, $arrData );

                        break;

                    case 'dbColumn':

                        $varValue = DbColumn::parseValue( $varDBValue, $arrField, $arrData );

                        break;
                }

                if ( $blnJustStrings && is_array( $varValue ) ) {

                    $varValue = implode( ', ', $varValue );
                }

                $arrData[ $strFieldname ] = Toolkit::isEmpty( $varValue ) ? $varDBValue : $varValue;
            }
        }

        return $arrData;
    }

    
    public static function createPanelLayout( $arrPanelLayout ) {

        $arrPanelLayout = is_array( $arrPanelLayout ) ? $arrPanelLayout : [];
        $strPanelLayout = implode( ',', $arrPanelLayout );

        if ( strpos( $strPanelLayout, 'filter' ) !== false ) {

            $strPanelLayout = preg_replace( '/,/' , ';', $strPanelLayout, 1 );
        }

        return $strPanelLayout;
    }


    public static function getLabelValue( $varValue, $strFallback ) {

        if ( Toolkit::isEmpty( $varValue ) ) return $strFallback;
        if ( is_array( $varValue ) ) return $varValue[0] ?: '';
        if ( is_string( $varValue ) ) return $varValue ?: '';

        return $strFallback;
    }


    public static function getBackendModuleTablesByDoAttribute( $strDo ) {

        if ( is_array( $GLOBALS['BE_MOD'] ) && TL_MODE == 'BE' && !Toolkit::isEmpty( $strDo ) ) {

            foreach ( $GLOBALS['BE_MOD'] as $arrModules ) {

                foreach ( $arrModules as $strModulename => $arrModule ) {
                    
                    if ( $strModulename == $strDo ) {

                        return is_array( $arrModule['tables'] ) ? $arrModule['tables'] : [];
                    }
                }
            }
        }

        return [];
    }


    public static function strictMode() {

        if ( !isset( $GLOBALS['TL_CONFIG']['ctlg_strict_mode'] ) || !is_bool( $GLOBALS['TL_CONFIG']['ctlg_strict_mode'] )  ) return true;

        return $GLOBALS['TL_CONFIG']['ctlg_strict_mode'] ? true : false;
    }


    public static function dump( $varDump ) {

        echo '<pre>' . var_export( $varDump, true ) . '</pre>';
    }


    public static function flatter( $arrValues, &$arrFlattedArray = [] ) {

        foreach ( $arrValues as $strKey => $varValue ) {

            if ( is_array( $varValue ) ) {

                static::flatter( $varValue, $arrFlattedArray );
                continue;
            }

            $arrFlattedArray[] = [

                'key' => $strKey,
                'value' => $varValue
            ];
        }

        return $arrFlattedArray;
    }


    public static function hasDynAlias() {

        return is_bool( \Config::get('dynAlias') ) && \Config::get('dynAlias') === true;
    }


    public static function setTokens( $arrRow, $strName, &$arrTokens = [] ) {

        foreach ( $arrRow as $strFieldname => $strValue ) {

            $arrTokens[ $strName . $strFieldname ] = $strValue;
        }

        return $arrTokens;
    }


    public static function parsePseudoInserttag( $strValue = '', $arrData = [] ) {

        if ( !empty( $strValue ) && is_string( $strValue ) && strpos( $strValue, '{{' ) !== false ) {

            $arrTags = preg_split( '/{{(([^{}]*|(?R))*)}}/', $strValue, -1, PREG_SPLIT_DELIM_CAPTURE );
            $strTag = implode( '', $arrTags );

            if ( $strTag && isset( $arrData ) && is_array( $arrData ) ) {

                return Toolkit::isEmpty( $arrData[ $strTag ] ) ? '' : $arrData[ $strTag ];
            }
        }

        return $strValue;
    }


    public static function flatterWithoutKeyValue( $arrValues, &$arrReturn, $strPrefix = '' ) {

        if ( is_array( $arrValues ) && !empty( $arrValues ) ) {

            foreach ( $arrValues as $strField => $varValue ) {

                if ( is_array( $varValue ) ) {

                    static::flatterWithoutKeyValue( $varValue, $arrReturn, $strField );
                }

                else {

                    $arrReturn[ ( $strPrefix ? $strPrefix . '_' : '' ) . $strField ] = $varValue;
                }
            }
        }
    }
}