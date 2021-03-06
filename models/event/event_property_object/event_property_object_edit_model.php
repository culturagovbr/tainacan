<?php
/*
include_once (dirname(__FILE__) . '/../../../../../../wp-config.php');
include_once (dirname(__FILE__) . '/../../../../../../wp-load.php');
include_once (dirname(__FILE__) . '/../../../../../../wp-includes/wp-db.php');
*/
require_once(dirname(__FILE__) . '../../../event/event_model.php');
require_once(dirname(__FILE__) . '../../../property/property_model.php');

class EventPropertyObjectEdit extends EventModel {

    public function __construct() {
        $this->parent = get_term_by('name', 'socialdb_event_property_object_edit', 'socialdb_event_type');
        $this->permission_name = 'socialdb_collection_permission_edit_property_object';
    }

    /**
     * function generate_title($data)
     * @param string $data  Os dados vindo do formulario
     * @return ara  
     * 
     * Autor: Eduardo Humberto 
     */
    public function generate_title($data) {
        $collection = get_post($data['socialdb_event_collection_id']);
        $property_name = $data['socialdb_event_property_object_edit_name'];
        $property = get_term_by('id',$data['socialdb_event_property_object_edit_id'],'socialdb_property_type');


        if(trim($property->name)==trim($property_name)){
            $text = '';
            $newcategory = $data['socialdb_event_property_object_category_id'];
            $old_categories = get_term_meta($data['socialdb_event_property_object_edit_id'],'socialdb_property_object_category_id');
            $newrequired = $data['socialdb_event_property_object_edit_required'];
            $required = get_term_meta($data['socialdb_event_property_object_edit_id'],'socialdb_property_required',true);
            $newreverse = $data['socialdb_event_property_object_edit_is_reverse'];
            $reverse = get_term_meta($data['socialdb_event_property_object_edit_id'],'socialdb_property_object_is_reverse',true);
            $newcardinality = $data['socialdb_event_property_object_edit_cardinality'];
            $cardinality = get_term_meta($data['socialdb_event_property_object_edit_id'],'socialdb_property_object_cardinality',true);

            if($newcategory !== $old_categories){
                $new_categories = explode(',',$newcategory);
                $new_names = [];
                foreach ($new_categories as $new_category){
                    $new_names[] = get_term_by('id',$new_category,'socialdb_category_type')->name;
                }

                if($old_categories) {
                    $old_names = [];
                    foreach ($old_categories as $old_category) {
                        $old_names[] = get_term_by('id', $old_category, 'socialdb_category_type')->name;
                    }
                }
                $text .= __(" Alter relationship from " , "tainacan");
                $val = ($old_names) ? htmlentities(implode(',',$old_names)) : '(Vazio)';
                $text .=  ' : <i>'.$val.'</i> ';
                $text .= __('to ', 'tainacan');
                $val = ($new_names) ? htmlentities(implode(',',$new_names)) : '( Vazio )';
                $text .= '<i>'.$val.'</i><br>';
            }
            if($newrequired !== $required){
                $newrequired = ($newrequired === 'true') ? __('True','tainacan') : __('False','tainacan');
                $required = ($required === 'true') ? __('True','tainacan') : __('False','tainacan');
                $text .=  __('Alter required field from ', 'tainacan').' : <i>'. $required .'</i> '. __('to ', 'tainacan').' <i>'.$newrequired.'</i>&nbsp;&nbsp;<br>';
            }

            if($newreverse !== $reverse){
                $newreverse = ($newreverse === 'true') ? __('True','tainacan') : __('False','tainacan');
                $reverse = ($reverse === 'true') ? __('True','tainacan') : __('False','tainacan');
                $text .=  __('Alter reverse field from ', 'tainacan').' : <i>'. $reverse .'</i> '. __('to ', 'tainacan').' <i>'.$newreverse.'</i>&nbsp;&nbsp;<br>';
            }

            if($newcardinality !== $cardinality){
                $newcardinality = ($newcardinality === 'n') ? __('Multiple values','tainacan') : __('One value','tainacan');
                $cardinality = ($cardinality === 'n') ? __('Multiple values','tainacan') : __('One value','tainacan');
                $text .=  __('Alter cardinality from ', 'tainacan').' : <i>'. $cardinality .'</i> '. __('to ', 'tainacan').' <i>'.$newcardinality.'</i>&nbsp;&nbsp;<br>';
            }

            $title = __('Alter configuration from object property ', 'tainacan').' : <i>'.$property->name.'</i>&nbsp;&nbsp;<br> '.$text.
                __(' in the collection ', 'tainacan') .' '.' <b><a target="_blank" href="'.  get_the_permalink($collection->ID).'">'.$collection->post_title.'</a></b> ';
        }else{
            $title = __('Edit the object property ', 'tainacan') .'<br>'.' '.
                __('From','tainacan').' : <i>'.$property->name.'</i><br>'.' '.
                __('To','tainacan').' : <i>'.$property_name.'</i><br>'.' '.
                __(' in the collection ', 'tainacan') .' '.' <b><a target="_blank" href="'.  get_the_permalink($collection->ID).'">'.$collection->post_title.'</a></b> ';
        }
        return $title;
    }

    /**
     * function verify_event($data)
     * @param string $data  Os dados do evento a ser verificado
     * @param string $automatically_verified  Se o evento foi automaticamente verificado
     * @return array  
     * 
     * Autor: Eduardo Humberto 
     */
    public function verify_event($data,$automatically_verified = false) {
       $actual_state = get_post_meta($data['event_id'], 'socialdb_event_confirmed',true);
       if($actual_state!='confirmed'&&$automatically_verified||(isset($data['socialdb_event_confirmed'])&&$data['socialdb_event_confirmed']=='true')){// se o evento foi confirmado automaticamente ou pelos moderadores
           $data = $this->update_property($data['event_id'],$data,$automatically_verified);    
       }elseif($actual_state!='confirmed'){
           $this->set_approval_metas($data['event_id'], $data['socialdb_event_observation'], $automatically_verified);
           $this->update_event_state('not_confirmed', $data['event_id']);
           $data['msg'] = __('The event was successful NOT confirmed','tainacan');
           $data['type'] = 'success';
           $data['title'] = __('Success','tainacan');
       }else{
           $data['msg'] = __('This event is already confirmed','tainacan');
           $data['type'] = 'info';
           $data['title'] = __('Atention','tainacan');
       }
        $this->notificate_user_email(get_post_meta($data['event_id'], 'socialdb_event_collection_id',true),  get_post_meta($data['event_id'], 'socialdb_event_user_id',true), $data['event_id']);
       return json_encode($data);
    }
      /**
     * function update_post_status($data)
     * @param string $event_id  O id do evento que vai pegar os metas
     * @param string $data  Os dados do evento a ser verificado
     * @param string $automatically_verified  Se o evento foi automaticamente verificado
     * @return array    
     * 
     * Autor: Eduardo Humberto 
     */
    public function update_property($event_id,$data,$automatically_verified) {
        $propertyModel = new PropertyModel();
        // coloco os dados necessarios para criacao da propriedade
        $data['property_object_id'] = get_post_meta($event_id, 'socialdb_event_property_object_edit_id',true) ;
        $data['property_object_name'] = get_post_meta($event_id, 'socialdb_event_property_object_edit_name',true) ;
        $data['collection_id'] = get_post_meta($event_id, 'socialdb_event_collection_id',true) ;
        $data['property_object_category_id'] = get_post_meta($event_id, 'socialdb_event_property_object_category_id',true) ;
        $data['property_object_required'] = get_post_meta($event_id, 'socialdb_event_property_object_edit_required',true) ;
        $data['property_object_facet'] = get_post_meta($event_id, 'socialdb_event_property_object_edit_is_facet',true) ;
        $data['property_object_is_reverse'] = get_post_meta($event_id, 'socialdb_event_property_object_edit_is_reverse',true) ;
        $data['property_visualization'] = get_post_meta($event_id, 'socialdb_event_property_visualization',true) ;
        $data['property_locked'] = get_post_meta($event_id, 'socialdb_event_property_lock_field',true) ;
        $data['property_to_search_in'] = get_post_meta($event_id, 'socialdb_event_property_to_search_in',true) ;
        $data['property_avoid_items'] = get_post_meta($event_id, 'socialdb_event_property_avoid_items',true) ;
        $data['property_habilitate_new_item'] = get_post_meta($event_id, 'socialdb_event_property_habilitate_new_item',true) ;
        $data['property_default_value'] = get_post_meta($event_id, 'socialdb_event_property_default_value',true) ;
        if($data['property_object_is_reverse']=='true'){
           $data['property_object_reverse'] = get_post_meta($event_id, 'socialdb_event_property_object_edit_reverse',true) ;   
        }
        $data['property_category_id'] = get_term_meta($data['property_object_id'], 'socialdb_property_created_category',true) ;
        $data['socialdb_property_object_cardinality'] = get_post_meta($event_id, 'socialdb_event_property_object_edit_cardinality',true) ;

        // chamo a funcao do model de propriedade para fazer a insercao
        $result = json_decode($propertyModel->update_property_object($data));
        if(isset(get_term_by('id', $data['property_object_id'], 'socialdb_property_type')->term_id)){
            do_action('after_event_update_property_object',get_term_by('id', $data['property_object_id'], 'socialdb_property_type')->term_id,$event_id);
        }
        // verifying if is everything all right
        if (get_term_by('id', $data['property_object_id'], 'socialdb_property_type')&&$result->success!='false') {
            $this->set_approval_metas($data['event_id'], $data['socialdb_event_observation'], $automatically_verified);
            $this->update_event_state('confirmed', $data['event_id']);
            $data['msg'] = __('The event was successful','tainacan');
            $data['type'] = 'success';
            $data['title'] = __('Success','tainacan');
        } else {
            $this->update_event_state('invalid', $data['event_id']); // seto a o evento como invalido
            if(isset($result->msg)):
             $data['msg'] = $result->msg;
            else:
              $data['msg'] = __('Please fill the fields correctly!','tainacan');  
            endif;
            $data['type'] = 'error';
            $data['title'] = 'Erro';
        }
        //$this->notificate_user_email( $data['collection_id'],  get_current_user_id(), $event_id);
        return $data;
    }

}
