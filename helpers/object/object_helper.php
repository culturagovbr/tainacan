<?php
/*
 * Object Controller's view helper 
 * */

class ObjectHelper extends ViewHelper {

    public function add_tabs($collection_id = false) {
        if(!$collection_id)
            $collection_id = $this->collection_id;
        $tabs = unserialize(get_post_meta($collection_id, 'socialdb_collection_update_tab_organization', true));
        $default_tab = get_post_meta($collection_id, 'socialdb_collection_default_tab', true);
        if (!$tabs || empty($tabs) && !$default_tab):
            _t('Expand all', 1);
        else: ?>
            <input type="hidden" name="tabs_properties" id="tabs_properties"
                    value='<?php echo ($tabs && is_array($tabs)) ? json_encode($tabs) : ''; ?>'/>
            <!-- Abas para a Listagem dos metadados -->
            <ul id="tabs_item" class="nav nav-tabs" style="background: white">
                <li  role="presentation" class="active">
                    <a id="click-tab-default" href="#tab-default" aria-controls="tab-default" role="tab" data-toggle="tab">
                        <span  id="default-tab-title">
            <?php echo (!$default_tab) ? _e('Default', 'tainacan') : $default_tab ?>
                        </span>
                    </a>
                </li>
            </ul>
            <div id="tab-content-metadata" class="tab-content" style="background: white;">
                <div id="tab-default"  class="tab-pane fade in active" style="background: white;margin-bottom: 15px;">
                    <div class="expand-all-div"  onclick="open_accordeon('default')" >
                        <a class="expand-all-link" href="javascript:void(0)">
                             <?php _e('Expand all', 'tainacan') ?>&nbsp;&nbsp;<span class="caret"></span></a>
                    </div>
                    <hr>
                    <?php _e('Expand all', 'tainacan') ?>
                    <div id="accordeon-default" class="multiple-items-accordion" style="margin-top:-20px;"></div>
                </div>
            </div>    
        <?php
        endif;
    }

    public function setValidation($collection_id,$id,$slug) {
        $required = get_post_meta($collection_id, 'socialdb_collection_property_'.$id.'_required', true);
        if($required&&$required=='true'):
        ?>
        <!--a id='required_field_<?php echo $slug ?>' style="padding: 3px;">
            <span title="<?php echo __('This metadata is required!', 'tainacan') ?>" 
                         data-toggle="tooltip" data-placement="top" >*</span>
        </a>
        <a id='ok_field_<?php echo $slug ?>'  style="display: none;padding: 0px;margin-left: -30px;"  >
            &nbsp; <span class="glyphicon glyphicon-ok-circle" title="<?php echo __('Field filled successfully!', 'tainacan') ?>" 
                         data-toggle="tooltip" data-placement="top" ></span>
        </a-->
         <a id='required_field_<?php echo $slug; ?>' class="pull-right" 
            style="margin-right: 15px;color:red;" >
                 <span class="glyphicon glyphicon-remove"  title="<?php echo __('This metadata is required!','tainacan')?>" 
                data-toggle="tooltip" data-placement="top" ></span>
         </a>
         <a id='ok_field_<?php echo $slug; ?>' class="pull-right" style="display: none;margin-right: 15px;color:green;"  >
                 <span class="glyphicon glyphicon-ok" title="<?php echo __('Field filled successfully!','tainacan')?>" 
                data-toggle="tooltip" data-placement="top" ></span>
         </a>    
        <input type="hidden" id='core_validation_<?php echo $slug ?>' class='core_validation' value='false'>
        <input type="hidden" id='core_validation_<?php echo $slug ?>_message'
               value='<?php echo sprintf(__('The field license is required', 'tainacan'), $slug); ?>'>
         <input type="hidden" id='fixed_id_<?php echo $slug ?>'
               value='<?php echo $id; ?>'>
        <?php
        endif;
    }

    public function renderCollectionPagination($_total_items_, $items_per_page, $page_id, $proper_str, $extra_class) {
        if( $_total_items_> 10 ) {
            $_num_pages_ = ceil($_total_items_ / 10);
            ?>
            <!-- TAINACAN: div com a paginacao da listagem -->
            <div class="col-md-12 center_pagination <?php echo $extra_class; ?>">
                               
                <input type="hidden" id="number_pages" name="number_pages" value="<?php echo $_num_pages_; ?>">
                <div class="pagination_items col-md-4 pull-left">
                    <a href="javascript:void(0)" class="btn btn-default btn-sm first" data-action="first"><span class="glyphicon glyphicon-backward"></span><!--&laquo;--></a>
                    <a href="javascript:void(0)" class="btn btn-default btn-sm previous" data-action="previous"><span class="glyphicon glyphicon-step-backward"></span><!--&lsaquo;--></a>
                    <input type="text" readonly="readonly" data-max-page="0" class="pagination-count"
                           data-current-page="<?php if (isset($page_id)) echo $page_id; ?>" />
                    <a href="javascript:void(0)" class="btn btn-default btn-sm next" data-action="next"><span class="glyphicon glyphicon-step-forward"></span><!--&rsaquo;--></a>
                    <a href="javascript:void(0)" class="btn btn-default btn-sm last" data-action="last"><span class="glyphicon glyphicon-forward"></span><!--   &raquo; --></a>
                </div>

                <div class="col-md-4 center">
                    <?php echo $proper_str; ?> <span class="base-page-init"> 1 </span> -
                    <span class='per-page'> <?php echo $items_per_page ?> </span>
                    <?php _t(' of ', 1); ?> <span class="col-total-items"> <?php echo $_total_items_; ?> </span>
                </div>

                <div class="col-md-3 pull-right per_page">
                    <?php _t('Items per page:', 1); ?>
                    <select name="items-per-page" class="col-items-per-page">
                       <?php $this->getItemsPerPage(); ?>
                    </select>
                </div>

            </div>
        <?php }
    }
    
    private function getItemsPerPage() {
        // By default, let 10 items selected
        $_show_values = [5,8,10,15,25,50];
        foreach ($_show_values as $k => $vl) {
            if($k == 2) {
                $select = 'selected';
            } else {
                $select = "";
            }
            echo "<option value='$vl' $select> $vl </option>";
        }
    }
}
