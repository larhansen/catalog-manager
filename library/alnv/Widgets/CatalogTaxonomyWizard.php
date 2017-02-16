<?php

namespace CatalogManager;

class CatalogTaxonomyWizard extends \Widget {


    private $strTable = '';
    private $arrFields = [];
    private $arrTaxonomies = [];

    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';


    public function __set($strKey, $varValue) {

        switch ($strKey) {

            default:

                parent::__set( $strKey, $varValue );

                break;
        }
    }


    public function validate() {

        parent::validate();
    }


    public function generate() {

        $intIndex = 0;
        $this->import( 'DCABuilderHelper' );
        $strCommand = 'cmd_' . $this->strField;

        if ( \Input::get( $strCommand ) && is_numeric( \Input::get('cid') ) && \Input::get('id') == $this->currentRecord ) {

            $this->import('Database');

            switch ( \Input::get( $strCommand ) ) {

                case 'addQuery':

                    if ( !$this->varValue['table'] ) break;

                    $this->varValue['query'][] = [

                        'value' => '',
                        'operator' => 'equal',
                        'field' => $this->varValue['table']
                    ];

                    unset( $this->varValue['table'] );

                    break;
            }

            $this->Database->prepare( sprintf( 'UPDATE %s SET `%s`=? WHERE id=? ', \Input::get('table'), $this->strField ) )->execute( serialize( $this->varValue ), $this->currentRecord );
            $this->redirect( preg_replace( '/&(amp;)?cid=[^&]*/i', '', preg_replace(' /&(amp;)?' . preg_quote( $strCommand, '/' ) . '=[^&]*/i', '', \Environment::get('request') ) ) );
        }

        if ( !$this->varValue ) $this->varValue = [];

        if ( !empty( $this->taxonomyTable ) && is_array( $this->taxonomyTable ) ) {

            $this->import( $this->taxonomyTable[0] );
            $this->strTable = $this->{$this->taxonomyTable[0]}->{$this->taxonomyTable[1]}( $this->objDca );
        }

        if ( !empty( $this->taxonomyEntities ) && is_array( $this->taxonomyEntities ) ) {

            $this->import( $this->taxonomyEntities[0] );
            $this->arrTaxonomies = $this->{$this->taxonomyEntities[0]}->{$this->taxonomyEntities[1]}( $this->objDca, $this->strTable );
        }

        $this->arrFields = array_keys( $this->arrTaxonomies );
        $this->arrTaxonomies = $this->DCABuilderHelper->convertCatalogFields2DCA( $this->arrTaxonomies );

        $this->cleanUpValues();

        $strHeadTemplate =

            '<table>'.
                '<thead>'.
                    '<tr>'.
                        '<td>select field</td>'.
                        '<td>&nbsp;</td>'.
                    '</tr>'.
                '</thead>'.
                '<tbody>'.
                    '<tr>'.
                        '<td>' . $this->getFieldSelector() . '</td>'.
                        '<td style="white-space:nowrap;padding-left:3px">' . $this->getAddButton() . '</td>'.
                    '</tr>'.
                '</tbody>'.
            '</table>';


        $strRowTemplate = '';

        if ( !empty( $this->varValue['query'] ) && is_array( $this->varValue['query'] ) ) {

            foreach ( $this->varValue['query'] as $arrQuery ) {

                if ( !empty( $arrQuery[0] ) && is_array( $arrQuery[0] ) ) {
                    
                    foreach ( $arrQuery as $intSubIndex => $arrOrQuery ) {

                        $strRowTemplate .= $this->generateQueryInputFields( $arrOrQuery, 'or', $intIndex, $intSubIndex );
                    }

                    $intIndex++;

                    continue;
                }

                $strRowTemplate .= $this->generateQueryInputFields( $arrQuery, 'and', $intIndex, null );
                $intIndex++;
            }
        }

        $strTemplate =
            '<table>'.
                '<thead>'.
                    '<tr>'.
                        '<td>Field</td>'.
                        '<td>Operator</td>'.
                        '<td>Value</td>'.
                        '<td>&nbsp;</td>'.
                        '<td>&nbsp;</td>'.
                    '</tr>'.
                '</thead>'.
                '<tbody>'.
                    $strRowTemplate.
                '</tbody>'.
            '</table>';

        
        return $strHeadTemplate.$strTemplate;
    }


    protected function generateQueryInputFields( $arrQuery, $strType, $intIndex, $intSubIndex ) {

        $strID = $this->strId . '_query_'. $intIndex;
        $strName = $this->strId . '[query]['. $intIndex .']';
        $strPaddingStyle = $strType == 'or' ? 'style="white-space:nowrap; padding-left:15px"' : '';

        $strFieldTemplate =
            '<tr>'.
                '<td '. $strPaddingStyle .'><select name="%s" id="%s" class="ctlg_select">%s</select></td>'.
                '<td '. $strPaddingStyle .'><select name="%s" id="%s" class="ctlg_select tl_chosen">%s</select></td>'.
                '<td '. $strPaddingStyle .'><input type="text" name="%s" id="%s" value="%s" class="ctlg_text"></td>'.
                '<td style="white-space:nowrap;padding-left:3px">'. $this->getOrButton() .'</td>'.
                '<td style="white-space:nowrap;padding-left:3px">'. $this->getDeleteButton() .'</td>'.
            '</tr>';

        if ( !is_null( $intSubIndex ) && $intSubIndex ) {

            $strID .= '_' . $intSubIndex;
            $strName .= '['.$intSubIndex.']';
        }

        return sprintf(

            $strFieldTemplate,
            $strName . '[field]',
            $strID,
            $this->getFieldOption( $arrQuery ),
            $strName . '[operator]',
            $strID,
            $this->getOperatorOptions( $arrQuery ),
            $strName . '[value]',
            $strID,
            $arrQuery['value']
        );
    }


    protected function getFieldOption( $arrQuery ) {

        return sprintf( '<option value="%s" selected>%s</option>', $arrQuery['field'], $arrQuery['field'] );
    }


    protected function getOperatorOptions( $arrQuery ) {

        $strOperatorsOptions = '';
        $arrOperators = [

            'equal',
            'not',
            'regexp',
            'gt',
            'gte',
            'lt',
            'lte',
            'contain',
            'between'
        ];

        foreach ( $arrOperators as $strOperator ) {

            $strOperatorsOptions .= sprintf( '<option value="%s" %s>%s</option>', $strOperator, ( $arrQuery['operator'] == $strOperator ? 'selected' : '' ), $strOperator );
        }

        return $strOperatorsOptions;
    }


    protected function cleanUpValues() {

        $arrValues = [];

        if ( !empty( $this->varValue['query'] ) && is_array( $this->varValue['query'] ) ) {

            foreach ( $this->varValue['query'] as $intIndex => $arrValue ) {
                
                $arrQuery = [

                    'field' => $arrValue['field'],
                    'value' => $arrValue['value'],
                    'operator' => $arrValue['operator']
                ];

                if ( count( $arrValue ) > 3 ) {

                    $arrValues[ $intIndex ][] = $arrQuery;

                    foreach ( $arrValue as $strKey => $strValue ) {

                        if ( is_array( $strValue ) ) {

                            $arrValues[ $intIndex ][] = $strValue;
                        }
                    }
                }

                else {

                    $arrValues[ $intIndex ] = $arrQuery;
                }
            }
        }

        $this->varValue['query'] = $arrValues;
    }


    protected function getFieldSelector() {

        return sprintf(
            '<select name="%s" id="%s" class="tl_select tl_chosen" onchange="Backend.autoSubmit(\'tl_module\');">%s</select>',
            $this->strId . '[table]',
            $this->strId . '_table',
            $this->getFieldOptions()
        );
    }


    protected function getFieldOptions() {

        $strOptions = '<option value="">-</option>';

        foreach (  $this->arrFields as $strField ) {

            $strOptions .= sprintf(

                '<option value="%s" %s>%s</option>',
                $strField,
                ( $this->varValue['table'] == $strField ? 'selected' : '' ),
                $this->arrTaxonomies[$strField]['label'][0] ? $this->arrTaxonomies[$strField]['label'][0] : $strField
            );
        }

        return $strOptions;
    }


    protected function getDeleteButton() {

        return ' <a href="" title="" onclick="return false">' . \Image::getHtml('delete.gif', 'delete query') .'</a>';
    }


    protected function getAddButton() {

        return ' <a href="'. \Environment::get('indexFreeRequest') .'&amp;cid=0&amp;cmd_'. $this->strId .'=addQuery" title="create new query" onclick="">' . \Image::getHtml('copy.gif', 'add query') .'</a>';
    }


    protected function getOrButton() {

        return ' <a href="" title="" onclick="return false">' . \Image::getHtml('copychilds.gif', 'add or Query') .'</a>';
    }
}