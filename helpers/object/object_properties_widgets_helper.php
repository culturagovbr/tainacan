<?php
/*
 * Object Controller's view helper 
 * */
class ObjectWidgetsHelper extends ViewHelper {
    
    /**
     * 
     * @param array $properties_compounds
     */
    public function list_properties_compounds($properties_compounds,$object_id,$references) {
        include_once ( dirname(__FILE__).'/../../views/object/js/properties_compounds_js.php');
        $result = [];
        $coumpounds_id = [];
        if (isset($properties_compounds)):
            foreach ($properties_compounds as $property) { 
               $result['ids'][] = $property['id']; ?>
               <div id="meta-item-<?php echo $property['id']; ?>"  class="form-group">
                    <h2>
                        <?php echo $property['name']; ?>
                        <?php 
                            if(has_action('modificate_label_insert_item_properties')):
                                do_action('modificate_label_insert_item_properties', $property);
                            endif;
                            //acao para modificaco da propriedade de objeto na insercao do item
                            if(has_action('modificate_insert_item_properties_compounds')): 
                                     do_action('modificate_insert_item_properties_compounds',$property,$object_id,'property_value_'. $property['id'] .'_'.$object_id.'_add'); 
                            endif;
                            if ($property['metas']['socialdb_property_help']&&!empty(trim($property['metas']['socialdb_property_help']))) {
                                ?>
                                <a class="pull-right" 
                                    style="margin-right: 20px;" >
                                     <span title="<?php echo $property['metas']['socialdb_property_help'] ?>" 
                                           data-toggle="tooltip" 
                                           data-placement="bottom" 
                                           class="glyphicon glyphicon-question-sign"></span>
                                </a>
                                <?php  
                            }
                            if ($property['metas']['socialdb_property_required']&&$property['metas']['socialdb_property_required'] == 'true') {
                                ?>
                                <a id='required_field_<?php echo $property['id']; ?>' style="padding: 3px;margin-left: -30px;" >
                                        <span class="glyphicon glyphicon glyphicon-star" title="<?php echo __('This metadata is required!','tainacan')?>" 
                                       data-toggle="tooltip" data-placement="top" ></span>
                                </a>
                                <a id='ok_field_<?php echo $property['id']; ?>'  style="display: none;padding: 3px;margin-left: -30px;" >
                                        <span class="glyphicon  glyphicon-ok-circle" title="<?php echo __('Field filled successfully!','tainacan')?>" 
                                       data-toggle="tooltip" data-placement="top" ></span>
                                </a>
                                <input type="hidden" 
                                         id='core_validation_<?php echo $property['id']; ?>' 
                                         class='core_validation' 
                                         value='false'>
                                <input type="hidden" 
                                         id='core_validation_<?php echo $property['id']; ?>_message'  
                                         value='<?php echo sprintf(__('The field %s is required','tainacan'),$property['name']); ?>'>
                                <script> set_field_valid(<?php echo $property['id']; ?>,'core_validation_<?php echo $property['id']; ?>') </script> 
                                <?php  
                            }
                            ?>
                    </h2> 
                    <?php $cardinality = $this->render_cardinality_property($property);   ?>
                    <?php $properties_compounded = $property['metas']['socialdb_property_compounds_properties_id']; ?>
                    <?php $class = 'col-md-'. (12/count($properties_compounded)); ?>
                    <div class="form-group">                        
                        <?php for($i = 0; $i<$cardinality;$i++): ?>
                            <div id="container_field_<?php echo $property['id']; ?>_<?php echo $i; ?>" 
                                 class=" col-md-12"
                                 style="padding-bottom: 10px;<?php echo ($i===0||(is_array($property['metas']['value'])&&$i<count($property['metas']['value']))) ? 'display:block': 'display:none'; ?>">
                                <?php foreach ($properties_compounded as $property_compounded): $coumpounds_id[] = $property_compounded['id']; ?>
                                <input  type="hidden" 
                                        id='core_validation_<?php echo $property_compounded['id']; ?>' 
                                        class='core_validation' 
                                        value='false'>
                                <div style="padding-bottom: 15px; " class="<?php echo $class ?>">
                                        <input type="hidden" 
                                                name="cardinality_compound_<?php echo $property_compounded['id']; ?>" 
                                                id="cardinality_compound_<?php echo $property_compounded['id']; ?>"
                                                value="<?php echo $cardinality; ?>"> 
                                        <?php 
                                        if(isset($property_compounded['metas']['socialdb_property_data_widget'])): 
                                            $this->widget_property_data($property_compounded, $i,$references);
                                        elseif(isset($property_compounded['metas']['socialdb_property_object_category_id'])): 
                                            $this->widget_property_object($property_compounded, $i,$references);
                                        elseif(isset($property_compounded['metas']['socialdb_property_term_widget'])): 
                                            $this->widget_property_term($property_compounded, $i,$references);
                                        endif; 
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                                <?php echo $this->render_button_cardinality($property,$i) ?>     
                            </div>  
                        <?php endfor; ?>
                        <input type="hidden" 
                               name="compounds_<?php echo $property['id']; ?>" 
                               id="compounds_<?php echo $property['id']; ?>"
                               value="<?php echo implode(',', $coumpounds_id); ?>"> 
                    </div>     
                </div>   
               <?php
            }
        ?>
        <input type="hidden" 
            name="properties_compounds" 
            id="properties_compounds"
            value="<?php echo implode(',', $result['ids']); ?>"> 
        <?php
        endif;    
    }
    
    /**
     * busca o widget para o os metadados de texto
     * @param array $property
     * @param int $i O indice do for da cardinalidade
     */
    public function widget_property_data($property,$i,$references) {
        if ($property['type'] == 'text') { ?>     
            <input type="text" 
                   id="form_edit_autocomplete_value_<?php echo $property['id']; ?>" 
                   class="form-control form_autocomplete_value_<?php echo $property['id']; ?>" 
                   value="<?php if ($property['metas']['value']) echo (isset($property['metas']['value'][$i]) ? $property['metas']['value'][$i] : ''); ?>"
                   name="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>">
        <?php }elseif ($property['type'] == 'textarea') { ?>   
            <textarea class="form-control form_autocomplete_value_<?php echo $property['id']; ?>"
                      rows="10"
                      id="form_edit_autocomplete_value_<?php echo $property['id']; ?>" 
                      name="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>" ><?php if ($property['metas']['value']) echo (isset($property['metas']['value'][$i]) ? $property['metas']['value'][$i] : ''); ?></textarea>
        <?php }elseif ($property['type'] == 'numeric') { ?>   
            <input  type="number" 
                    class="form-control form_autocomplete_value_<?php echo $property['id']; ?>"
                    onkeypress='return onlyNumbers(event)'
                    id="form_edit_autocomplete_value_<?php echo $property['id']; ?>" 
                    name="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>" 
                    value="<?php if ($property['metas']['value']) echo $property['metas']['value'][0]; ?>">
        <?php }elseif ($property['type'] == 'autoincrement') { ?>   
            <input disabled="disabled"  
                   type="number" 
                   class="form-control" 
                   name="hidded_<?php echo $property['id']; ?>" 
                   value="<?php if ($property['metas']['value']) echo (isset($property['metas']['value'][$i]) ? $property['metas']['value'][$i] : ''); ?>">
        <?php } else if ($property['type'] == 'date' && !has_action('modificate_edit_item_properties_data')) { ?>
            <script>
               $(function() {
                   $( "#socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>" ).datepicker({
                       dateFormat: 'dd/mm/yy',
                       dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'],
                       dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
                       dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
                       monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
                       monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
                       nextText: 'Próximo',
                       prevText: 'Anterior',
                       showOn: "button",
                       buttonImage: "http://jqueryui.com/resources/demos/datepicker/images/calendar.gif",
                       buttonImageOnly: true
                   });
               });
           </script>    
           <input 
               style="margin-right: 5px;" 
               size="13" 
               class="input_date form_autocomplete_value_<?php echo $property['id']; ?>" 
               value="<?php if ($property['metas']['value']) echo (isset($property['metas']['value'][$i]) ? $property['metas']['value'][$i] : ''); ?>"
               type="text" 
               id="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>" 
               name="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>">   
        <?php
        }
        // gancho para tipos de metadados de dados diferentes
        else if (has_action('modificate_edit_item_properties_data')) {
            do_action('modificate_edit_item_properties_data', $property);
            return false;
        } else {
            ?>
            <input type="text"  
                   value="<?php if ($property['metas']['value']) echo (isset($property['metas']['value'][$i]) ? $property['metas']['value'][$i] : ''); ?>" 
                   class="form-control form_autocomplete_value_<?php echo $property['id']; ?>" 
                   name="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>" >
        <?php
        }
    }
    /**
     * busca o widget para o os metadados de termo
     * @param array $property
     * @param int $i O indice do for da cardinalidade
     */
    public function widget_property_object($property,$i,$references) {
        ?>
        <input type="hidden" 
                        id="cardinality_<?php echo $property['id']; ?>_<?php echo $object_id; ?>"  
                        value="<?php echo $this->render_cardinality_property($property);   ?>">            
        <input type="text" 
               onkeyup="autocomplete_object_property_compound('<?php echo $property['id']; ?>', '<?php echo $i; ?>');" 
               id="autocomplete_value_<?php echo $property['id']; ?>_<?php echo $i; ?>" 
               placeholder="<?php _e('Type the three first letters of the object of this collection ', 'tainacan'); ?>"  
               class="chosen-selected form-control"  />    

        <select onclick="clear_select_object_property_compound(this,'<?php echo $property['id']; ?>', '<?php echo $i; ?>');" 
                id="property_value_<?php echo $property['id']; ?>_<?php echo $i; ?>_edit" 
                multiple class="chosen-selected2 form-control" 
                style="height: auto;" 
                name="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>[]"
                <?php 
                    if ($property['metas']['socialdb_property_required'] == 'true'): 
                        echo 'required="required"';
                    endif;
                ?> >
                <?php 
                    if (!empty($property['metas']['objects'])) { ?>     
                        <?php foreach ($property['metas']['objects'] as $object) { ?>
                            <?php if (isset($property['metas']['value']) && !empty($property['metas']['value']) && in_array($object->ID, $property['metas']['value'])): // verifico se ele esta na lista de objetos da colecao   ?>    
                                 <option selected='selected' value="<?php echo $object->ID ?>"><?php echo $object->post_title ?></span>
                        <?php endif; ?>
                    <?php } ?> 
                <?php 
                    }else { 
                ?>   
                    <option value=""><?php _e('No objects added in this collection', 'tainacan'); ?></option>
                <?php 
                    } 
                ?>       
        </select>    
        <?php
    }
    
    /**
     * busca o widget para o os metadados de relacionamento
     * @param array $property
     * @param int $i O indice do for da cardinalidade
     */
    public function widget_property_term($property,$i,$references) {
        if ($property['type'] == 'radio') {
            $references['properties_terms_radio'][] = $property['id'];
            ?>
            <div id='field_property_term_<?php echo $property['id']; ?>_<?php echo $i; ?>'></div>
            <?php
        } elseif ($property['type'] == 'tree') {
            $references['properties_terms_tree'][] = $property['id'];
            ?>
            <button type="button"
                onclick="showModalFilters('add_category','<?php echo get_term_by('id', $property['metas']['socialdb_property_term_root'] , 'socialdb_category_type')->name ?>',<?php echo $property['metas']['socialdb_property_term_root'] ?>,'field_property_term_<?php echo $property['id']; ?>')" 
                class="btn btn-primary btn-xs"><?php _e('Add Category','tainacan'); ?>
            </button>
            <br><br>
            <div class="row">
                <div style='height: 150px;' 
                     class='col-lg-12'  
                     id='field_property_term_<?php echo $property['id']; ?>_<?php echo $i; ?>'>
                </div>
                <input type="hidden" 
                       id='field_property_term_<?php echo $property['id']; ?>_<?php echo $i; ?>'
                       name="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>[]" 
                       value="">
            </div>
            <?php
        }elseif ($property['type'] == 'selectbox') {
            $references['properties_terms_selectbox'][] = $property['id'];
            ?>
            <select class="form-control" 
                    name="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>[]" 
                    onchange="edit_validate_selectbox(this,'<?php echo $property['id']; ?>')"
                    id='field_property_term_<?php echo $property['id']; ?>_<?php echo $i; ?>' >
            </select>
            <?php
        }elseif ($property['type'] == 'checkbox') {
            $references['properties_terms_checkbox'][] = $property['id']; ?>
            <div id='field_property_term_<?php echo $property['id']; ?>_<?php echo $i; ?>'></div>
            <?php
        } elseif ($property['type'] == 'multipleselect') {
            $references['properties_terms_multipleselect'][] = $property['id'];
            ?>
             <select size='1' 
                multiple 
                onclick="validate_multipleselectbox(this,'<?php echo $property['id']; ?>')"
                class="form-control field_property_term_<?php echo $property['id']; ?>" 
                name="socialdb_property_<?php echo $property['id']; ?>_<?php echo $i; ?>[]" 
                <?php 
                if ($property['metas']['socialdb_property_required'] == 'true'): 
                    echo 'required="required"';
                endif;
                ?>>
             </select>
                    <?php
        }elseif ($property['type'] == 'tree_checkbox') {
            $references['properties_terms_treecheckbox'][] = $property['id']; ?>
            <button type="button"
                onclick="showModalFilters('add_category','<?php echo get_term_by('id', $property['metas']['socialdb_property_term_root'] , 'socialdb_category_type')->name ?>',<?php echo $property['metas']['socialdb_property_term_root'] ?>,'field_property_term_<?php echo $property['id']; ?>_<?php echo $i; ?>')" 
                class="btn btn-primary btn-xs"><?php _e('Add Category','tainacan'); ?>
            </button>
            <br><br>
            <div class="row">
                <div style='height: 150px;' 
                     class='col-lg-12'  
                     id='field_property_term_<?php echo $property['id']; ?>_<?php echo $i; ?>'>
                </div>
                <div id='socialdb_propertyterm_<?php echo $property['id']; ?>_<?php echo $i; ?>' ></div>
            </div>
            <?php
        }
    }

}