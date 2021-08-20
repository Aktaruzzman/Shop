
<?php

class Blogs extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'blogs';
        $this->_primary_key = 'id';
        $this->_order_by = 'id';
        $this->_order_type = 'ASC';
        $this->_default = 'default';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
    }
    public function validate()
    {
        $is_title_en_unique = $this->check_unique('title_en', $this->input->post('title_en')) ? "|is_unique[pages.title_en]" : "";
        $is_title_bn_unique = $this->check_unique('title_bn', $this->input->post('title_bn')) ? "|is_unique[pages.title_bn]" : "";
        if (!empty($this->input->post('id'))) {
            $existing = $this->get($this->input->post('id'), true);
            $is_title_en_unique = strtolower($this->input->post('title_en')) == strtolower($existing->title_en) ? "" : $is_title_en_unique;
            $is_title_bn_unique = trim($this->input->post('title_bn')) == trim($existing->title_bn) ? "" : $is_title_bn_unique;
        }
        $this->form_validation->set_rules('title_en', lang('name') . ' ' . lang('english'), 'required' . $is_title_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('title_bn', lang('name') . ' ' . lang('bangla'), 'required' . $is_title_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('description_en', lang('description') . ' ' . lang('english'), 'required' . $is_title_en_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('description_bn', lang('description') . ' ' . lang('bangla'), 'required' . $is_title_bn_unique, array('required' => lang('field_required_msg')));
        $this->form_validation->set_rules('order', lang('order'), 'numeric');
        return $this->form_validation->run();
    }
    public function arrange_entity($entity)
    {
        if (array_key_exists('title_en', $entity) && array_key_exists('title_bn', $entity)) $entity['title'] = $this->encode(['en' => $entity['title_en'], 'bn' => $entity['title_bn']]);
        if (array_key_exists('description_en', $entity) && array_key_exists('description_bn', $entity)) $entity['description'] = $this->encode(['en' => $entity['description_en'], 'bn' => $entity['description_en']]);
        if (array_key_exists('meta_keys_en', $entity) && array_key_exists('meta_keys_bn', $entity)) $entity['meta_keys'] = $this->encode(['en' => $entity['meta_keys_en'], 'bn' => $entity['meta_keys_bn']]);
        if (array_key_exists('meta_desc_en', $entity) && array_key_exists('meta_desc_en', $entity)) $entity['meta_desc'] = $this->encode(['en' => $entity['meta_desc_en'], 'bn' => $entity['meta_desc_bn']]);
        if (!empty($entity['id'])) {
            if ($this->hasField('slug') && $this->get($entity['id'], true, true)->slug !== url_title($entity['title_en'], '-', true))
                $entity['slug'] = $this->slug($entity['title_en'], true);
        } else {
            if ($this->hasField('slug')) $entity['slug'] = $this->slug($entity['title_en'], true);
        }

        return $entity;
    }
    public function build_entity($entity)
    {
        $entity->title = $this->decode($entity->title);
        $entity->description = $this->decode($entity->description);
        $entity->meta_keys = $this->decode($entity->meta_keys);
        $entity->meta_desc = $this->decode($entity->meta_desc);
        $entity->photos['full'] = !empty($entity->photo) ? site_url('/uploads/blog/' . $entity->photo) : './img/no-image.png';
        $entity->photos['thumb'] = !empty($entity->thumb) ? site_url('/uploads/blog/thumbnail/' . $entity->thumb) : './img/no-image.png';
        return $entity;
    }
}
