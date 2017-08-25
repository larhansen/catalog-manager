<?php

namespace CatalogManager;

class DcBuilder extends CatalogController {


    protected $strID;
    protected $strTable;
    protected $arrFields = [];
    protected $arrCatalog = [];
    protected $arrErrorTables = [];
    protected $arrOverwritten = [];
    protected $strPermissionType = '';

    protected $arrOperations = [

        'cut' => false,
        'copy' => false,
        'invisible' => false
    ];


    public function __construct( $arrCatalog ) {

        parent::__construct();

        $this->import( 'Database' );
        $this->import( 'IconGetter' );
        $this->import( 'CatalogDcExtractor' );
        $this->import( 'CatalogFieldBuilder' );
        $this->import( 'I18nCatalogTranslator' );

        $this->arrCatalog = $arrCatalog;
        $this->strID = $arrCatalog['id'];
        $this->strTable = $arrCatalog['tablename'];
        $this->strPermissionType = $arrCatalog['permissionType'];

        if ( !$this->strTable ) return null;

        $this->CatalogDcExtractor->initialize( $this->strTable );
        $this->CatalogFieldBuilder->initialize( $this->strTable );

        if ( \Input::get( 'do' ) && \Input::get( 'do' ) == $this->arrCatalog['tablename'] ) {

            $objReviseRelatedTables = new ReviseRelatedTables();

            if ( $objReviseRelatedTables->reviseCatalogTables( $this->arrCatalog['tablename'] , $this->arrCatalog['pTable'], $this->arrCatalog['cTables'] ) ) {

                foreach ( $objReviseRelatedTables->getErrorTables() as $strTable ) {

                    \Message::addError( sprintf( "Table '%s' can not be used as relation. Please delete all rows or create valid pid value.", $strTable ) );

                    $this->arrErrorTables[] = $strTable;

                    if ( $strTable == $this->arrCatalog['pTable'] ) {

                        $this->arrCatalog['pTable'] = '';
                    }

                    if ( in_array( $strTable , $this->arrCatalog['cTables'] ) ) {

                        $intPosition = array_search( $strTable, $this->arrCatalog['cTables'] );

                        unset( $this->arrCatalog['cTables'][ $intPosition ] );
                    }
                }
            }
        }
    }
    

    public function initializeI18n() {

        $this->I18nCatalogTranslator->initialize();
    }


    protected function determineOperations() {

        $arrOperations = [];

        if ( $this->arrCatalog['operations'] ) {

            $arrOperations = deserialize( $this->arrCatalog['operations'] );
        }

        if ( !empty( $arrOperations ) && is_array( $arrOperations ) ) {

            foreach ( $arrOperations as $strOperation ) {

                $this->arrOperations[ $strOperation ] = isset( $this->arrOperations[ $strOperation ] );
            }
        }
    }


    public function createDataContainerArray() {

        $this->initializeI18n();
        $this->determineOperations();

        $this->arrFields = $this->CatalogFieldBuilder->getCatalogFields( $this->strTable, true, null );

        $GLOBALS['TL_DCA'][ $this->strTable ] = [

            'config' => $this->getConfigDc(),

            'list' => [

                'label' => $this->getLabelDc(),
                'sorting' => $this->getSortingDc(),
                'operations' => $this->getOperationsDc(),
                'global_operations' => $this->getGlobalOperationsDc(),
            ],

            'palettes' => $this->getPalettesDc(),
            'fields' => $this->CatalogFieldBuilder->getDcFormatOnly()
        ];

        $GLOBALS['TL_LANG'][ $this->strTable ]['new'] = $this->I18nCatalogTranslator->getNewLabel();
        $GLOBALS['TL_LANG'][ $this->strTable ]['show'] = $this->I18nCatalogTranslator->getShowLabel();
    }


    protected function getConfigDc() {

        $arrReturn = [

            'label' => $this->I18nCatalogTranslator->get( 'module', $this->strTable, [ 'titleOnly' => true ] ),
            'dataContainer' => 'Table',

            'oncut_callback' => [],
            'onload_callback' => [],
            'onsubmit_callback' => [],
            'ondelete_callback' => [],

            'sql' => [

                'keys' => [

                    'id' => 'primary'
                ]
            ]
        ];

        if ( $this->arrCatalog['useGeoCoordinates'] ) {

            $arrReturn['onsubmit_callback'][] = [ 'CatalogManager\DCACallbacks', 'generateGeoCords' ];
        }

        foreach ( $this->arrFields as $arrField ) {

            if ( !$arrField['useIndex'] ) continue;

            $arrReturn['sql']['keys'][ $arrField['fieldname'] ] = $arrField['useIndex'];
        }

        if ( $this->CatalogFieldBuilder->shouldBeUsedParentTable() ) {

            $arrReturn['ptable'] = $this->arrCatalog['pTable'];
        }

        if ( $this->arrCatalog['addContentElements'] ) {

            if ( !is_array( $this->arrCatalog['cTables'] ) ) {

                $this->arrCatalog['cTables'] = [];
            }

            $this->arrCatalog['cTables'][] = 'tl_content';
        }

        if ( !empty( $this->arrCatalog['cTables'] ) && is_array( $this->arrCatalog['cTables'] ) ) {

            $arrReturn['ctable'] = $this->arrCatalog['cTables'];
        }

        if ( $this->arrCatalog['permissionType'] ) {

            $arrReturn['onload_callback'][] = function() {

                $objDCAPermission = new DCAPermission();
                $objDCAPermission->checkPermission( $this->strTable , $this->strTable, $this->strTable . 'p', $this->strPermissionType );
            };
        }

        $arrReturn['oncut_callback'][] = [ 'CatalogManager\DCACallbacks', 'onCutCallback' ];
        $arrReturn['onsubmit_callback'][] = [ 'CatalogManager\DCACallbacks', 'onSubmitCallback' ];
        $arrReturn['ondelete_callback'][] = [ 'CatalogManager\DCACallbacks', 'onDeleteCallback' ];

        return $arrReturn;
    }


    protected function getLabelDc() {

        $arrReturn = $this->CatalogDcExtractor->setDcLabelByMode( $this->arrCatalog['mode'], $this->arrCatalog );

        if ( isset( $arrReturn['label_callback'] ) ) {

            $arrReturn['label_callback'] = function ( $arrRow, $strLabel ) {

                $objDCACallback = new DCACallbacks();

                return $objDCACallback->labelCallback( $this->arrCatalog['labelFormat'], $this->arrFields, $arrRow, $strLabel );
            };
        }

        if ( isset( $arrReturn['group_callback'] ) ) {

            $arrReturn['group_callback'] = function ( $strGroup, $strMode, $strField, $arrRow, $dc ) {

                $objDCACallback = new DCACallbacks();

                return $objDCACallback->groupCallback( $this->arrCatalog['groupFormat'], $this->arrFields, $strGroup, $strMode, $strField, $arrRow, $dc );
            };
        }

        if ( $this->arrCatalog['mode'] == '5' ) {

            $arrReturn['label_callback'] = function( $arrRow, $strLabel, \DataContainer $dc = null, $strImageAttribute = '', $blnReturnImage = false, $blnProtected = false ) {

                $objDCACallback = new DCACallbacks();
                $strTemplate = $this->IconGetter->setTreeViewIcon( $this->arrCatalog['tablename'], $arrRow, $strLabel, $dc, $strImageAttribute, $blnReturnImage, $blnProtected );

                if ( $this->arrCatalog['useOwnLabelFormat'] ) {

                    $strTemplate .= !Toolkit::isEmpty( $this->arrCatalog['labelFormat'] ) ? $this->arrCatalog['labelFormat'] : $strTemplate;
                }

                else {

                    if ( !$arrRow['pid'] ) $strTemplate .= ' <strong>' . $strLabel . '</strong>';
                    else $strTemplate .= ' <span>' . $strLabel . '</span>';
                }

                return $objDCACallback->labelCallback( $strTemplate, $this->arrFields, $arrRow, $strLabel );
            };
        }

        return $arrReturn;
    }


    protected function getSortingDc() {

        $arrReturn = $this->CatalogDcExtractor->setDcSortingByMode( $this->arrCatalog['mode'], $this->arrCatalog );
        $arrReturn['panelLayout'] = Toolkit::createPanelLayout( $this->arrCatalog['panelLayout'] );

        if ( isset( $arrReturn['child_record_callback'] ) ) {

            $arrLabelFields = [ 'title' ];

            if ( is_array( $this->arrCatalog['labelFields'] ) && !empty( $this->arrCatalog['labelFields'] ) ) {

                $arrLabelFields = $this->arrCatalog['labelFields'];
            }

            $arrReturn['child_record_callback'] = function ( $arrRow ) use ( $arrLabelFields ) {

                $strLabel = $arrLabelFields[0];
                $strTemplate = '##' . $strLabel . '##';
                $objDCACallback = new DCACallbacks();

                if ( $this->arrCatalog['useOwnLabelFormat'] ) {

                    $strTemplate = !Toolkit::isEmpty( $this->arrCatalog['labelFormat'] ) ? $this->arrCatalog['labelFormat'] : $strTemplate;
                }

                return $objDCACallback->childRecordCallback( $strTemplate, $this->arrFields, $arrRow, $strLabel );
            };
        }

        return $arrReturn;
    }


    protected function getOperationsDc() {

        $arrReturn = [

            'edit' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['edit'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ],

            'copy' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif'
            ],

            'cut' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ],

            'delete' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $this->I18nCatalogTranslator->getDeleteConfirmLabel() . '\'))return false;Backend.getScrollOffset()"'
            ],

            'toggle' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['toggle'],
                'icon' => 'visible.gif',
                'href' => sprintf( 'catalogTable=%s', $this->strTable ),
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility( this,%s,'. sprintf( "'%s'", $this->strTable ) .' )"',
                'button_callback' => [ 'DCACallbacks',  'toggleIcon' ]
            ],

            'show' => [

                'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ]
        ];

        if ( in_array( $this->arrCatalog['mode'], [ '4', '5' ] ) ) {

            $arrReturn['copy']['href'] = 'act=paste&amp;mode=copy';
        }

        else {
            
            unset( $arrReturn['cut'] );
        }

        foreach ( $this->arrOperations as $strOperation => $blnActive ) {

            if ( $strOperation == 'invisible' && !$blnActive ) {

                unset( $arrReturn[ 'toggle' ] );

                continue;
            }

            if ( !$blnActive && isset( $arrReturn[ $strOperation ] ) ) {

                unset( $arrReturn[ $strOperation ] );
            }
        }

        foreach ( $this->arrCatalog['cTables'] as $arrCTable ) {

            if ( in_array( $arrCTable, $this->arrErrorTables ) ) continue;

            $arrChildTable = [];
            $strOperationName = sprintf( 'go_to_%s', $arrCTable );

            $arrChildTable[ $strOperationName ] = [

                'href' => sprintf( 'table=%s', $arrCTable ),
                'label' => [ sprintf( $GLOBALS['TL_LANG']['catalog_manager']['operations']['goTo'][0], $arrCTable ), sprintf( $GLOBALS['TL_LANG']['catalog_manager']['operations']['goTo'][1], $arrCTable ) ],
                'icon' => $arrCTable !== 'tl_content' ?  $this->IconGetter->setCatalogIcon( $arrCTable ) : 'articles.gif'
            ];

            array_insert( $arrReturn, 1, $arrChildTable );
        }

        if ( !empty( $this->arrFields ) && is_array( $this->arrFields ) ) {

            foreach ( $this->arrFields as $arrField ) {

                if ( $arrField['multiple'] ) continue;

                if ( !$arrField['fieldname'] || !$arrField['type'] == 'checkbox' ) continue;

                if ( $arrField['enableToggleIcon'] ) {

                    $arrToggleIcon = [];
                    $strVisibleIcon = $this->IconGetter->setToggleIcon( $arrField['fieldname'], true );
                    $strInVisibleIcon = $this->IconGetter->setToggleIcon( $arrField['fieldname'], false );
                    $strHref = sprintf( 'catalogTable=%s&fieldname=%s&iconVisible=%s', $this->strTable, $arrField['fieldname'], $strVisibleIcon );

                    $arrToggleIcon[ $arrField['fieldname'] ] = [

                        'href' => $strHref,
                        'icon' => $strInVisibleIcon,
                        'button_callback' => [ 'DCACallbacks',  'toggleIcon' ],
                        'label' => &$GLOBALS['TL_LANG']['catalog_manager']['operations']['toggleIcon'],
                        'attributes' => 'onclick="Backend.getScrollOffset();return CatalogManager.CatalogToggleVisibility( this,%s,'. sprintf( "'%s'", $strVisibleIcon ) .', '. sprintf( "'%s'", $strInVisibleIcon ) .', '. sprintf( "'%s'", $strHref ) .' )"'
                    ];

                    array_insert( $arrReturn, count( $arrReturn ), $arrToggleIcon );
                }
            }
        }

        return $arrReturn;
    }


    protected function getGlobalOperationsDc() {

        return [

            'all' => [

                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ];
    }


    protected function getPalettesDc() {

        $strReturn = '';
        $arrTranslations = [];
        $strPalette = 'general_legend';
        $arrPalette = [ 'general_legend' => [] ];

        foreach ( $this->arrFields as $arrField ) {

            if ( Toolkit::isEmpty( $arrField['type'] ) ) continue;

            if ( !Toolkit::isEmpty( $arrField['title'] ) && $arrField['type'] == 'fieldsetStart' ) {

                $strPalette = $arrField['title'] . ( $arrField['isHidden'] ? ':hide' : '' );
                $arrTranslations[$strPalette] = $arrField['label'];
            }

            if ( Toolkit::isEmpty( $arrField['fieldname'] ) ) continue;

            if ( in_array( $arrField['fieldname'], Toolkit::invisiblePaletteFields() ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::columnOnlyFields() ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::excludeFromDc() ) ) continue;

            $arrPalette[$strPalette][] = $arrField['fieldname'];
        }

        if ( $this->arrOperations['invisible'] ) {

            $arrPalette['invisible_legend'] = Toolkit::invisiblePaletteFields();
        }

        foreach ( $arrPalette as $strLegend => $arrFields ) {

            $strHide = '';
            $strLegendName = $strLegend;
            $arrLegend = explode( ':', $strLegend );

            if ( is_array( $arrLegend ) ) {

                $strLegendName = $arrLegend[0];
                $strHide = Toolkit::isEmpty( $arrLegend[1] ) ? '' : ':hide';
            }

            $GLOBALS['TL_LANG'][ $this->strTable ][ $strLegendName ] = $this->I18nCatalogTranslator->get( 'legend', $strLegendName, [ 'title' => $arrTranslations[ $strLegend ] ] );
            $strReturn .= sprintf( '{%s%s},%s;', $strLegendName, $strHide, implode( ',', $arrFields ) );
        }

        return [ 'default' => $strReturn ];
    }
}