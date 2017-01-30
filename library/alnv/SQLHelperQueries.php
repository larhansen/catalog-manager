<?php

namespace CatalogManager;

class SQLHelperQueries extends CatalogController {

    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryBuilder' );
    }

    public function getCatalogByTablename( $strTablename ) {

        if ( !$strTablename ) return [];

        return $this->SQLQueryBuilder->execute([

            'table' => 'tl_catalog',

            'pagination' => [

                'limit' => 1,
                'offset' => 0,
            ],

            'where' => [

                [
                    'operator' => 'equal',
                    'field' => 'tablename',
                    'value' => $strTablename
                ]
            ]

        ])->row();
    }

    public function getCatalogFieldsByCatalogID( $strID, $arrCallback = [] ) {

        $arrFields = [];

        if ( !$strID ) return $arrFields;

        $objFields = $this->SQLQueryBuilder->execute([

            'table' => 'tl_catalog_fields',

            'orderBY' => [

                'order' => 'DESC',
                'field' => 'sorting'
            ],

            'where' => [

                [
                    'field' => 'pid',
                    'value' => $strID,
                    'operator' => 'equal'
                ]
            ]

        ]);

        $intIndex = 0;
        $intCount = $objFields->count();

        while ( $objFields->next() ) {

            $arrFields[ $objFields->id ] = $objFields->row();

            if ( !empty( $arrCallback ) && is_array( $arrCallback ) ) {

                $this->import( $arrCallback[0] );
                $arrFields[ $objFields->id ] = $this->{$arrCallback[0]}->{$arrCallback[1]}( $arrFields[ $objFields->id ], $intIndex, $intCount, $objFields->fieldname );
            }

            if ( $arrFields[ $objFields->id ] == null ) {

                continue;
            }

            $intIndex++;
        }

        return $arrFields;
    }
}