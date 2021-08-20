
<?php

class Geodefaults extends MY_Model
{

    function __construct()
    {
        $this->load->model('Geodivisions');
        $this->load->model('Geodistricts');
        $this->load->model('Geoupazillas');
        $this->load->model('Geoareas');
    }
    public function get_all()
    {
        $defaults['division'] = $this->get_division();
        $defaults['district'] = $this->get_district($defaults['division']->id);
        $defaults['upazilla'] = $this->get_upazilla($defaults['district']->id);
        $defaults['area'] = $this->get_upazilla($defaults['upazilla']->id);
        return $defaults;
    }
    public function get_division()
    {
        $result = $this->Geodivisions->get_by(['default' => true], TRUE, FALSE, 1, 0, NULL);
        if (!empty($result)) {
            $result->name = json_decode($result->name);
        } else {
            $result = $this->Geodivisions->get_by(NULL, TRUE, FALSE, 1, 0, NULL);
            if (!empty($result)) $result->name = json_decode($result->name);
        }
        return $result;
    }
    public function get_district($division_id)
    {
        $result = $this->Geodistricts->get_by(['default' => true, 'division_id' => $division_id], TRUE, FALSE, 1, 0, NULL);
        if (!empty($result)) {
            $result->name = json_decode($result->name);
        } else {
            $result = $this->Geodistricts->get_by(NULL, TRUE, FALSE, 1, 0, NULL);
            if (!empty($result)) $result->name = json_decode($result->name);
        }
        return $result;
    }
    public function get_upazilla($district_id)
    {
        $result = $this->Geoupazillas->get_by(['default' => true, 'district_id' => $district_id], TRUE, FALSE, 1, 0, NULL);
        if (!empty($result)) {
            $result->name = json_decode($result->name);
        } else {
            $result = $this->Geoupazillas->get_by(NULL, TRUE, FALSE, 1, 0, NULL);
            if (!empty($result)) $result->name = json_decode($result->name);
        }
        return $result;
    }
    public function get_area($upazilla_id)
    {
        $result = $this->Geoareas->get_by(['default' => true, 'upazilla_id' => $upazilla_id], TRUE, FALSE, 1, 0, NULL);
        if (!empty($result)) $result->name = json_decode($result->name);
        return $result;
    }
    public function get_union($upazilla_id)
    {
        $result = $this->Geounion->get_by(['default' => true, 'upazilla_id' => $upazilla_id], TRUE, FALSE, 1, 0, NULL);
        if (!empty($result)) $result->name = json_decode($result->name);
        return $result;
    }
}
