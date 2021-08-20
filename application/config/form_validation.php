<?php

$config = array(
    'contact' => array(
        array(
            'field' => 'name',
            'label' => 'Name',
            'rules' => 'required|alpha_numeric_spaces',
            'errors' => array(
                'required' => 'First name is required',
                'alpha_numeric_spaces' => 'Only letter and number allowed'
            ),
        ),
        array(
            'field' => 'email',
            'label' => 'Email Address',
            'rules' => 'required|valid_email'
        ),
        array(
            'field' => 'phone',
            'label' => 'Phone',
            'rules' => 'required|numeric'
        ),
        array(
            'field' => 'message',
            'label' => 'Message',
            'rules' => 'required|alpha_numeric_spaces'
        ),
        array(
            'field' => 'g-recaptcha-response',
            'label' => 'I am not a robot',
            'rules' => 'required'
        ),
    ),
    'reservation' => array(
        array(
            'field' => 'first_name',
            'label' => 'First Name',
            'rules' => 'required|alpha_numeric_spaces',
            'errors' => array(
                'required' => 'First name is required',
                'alpha_numeric_spaces' => 'Only letter and number allowed'
            ),
        ),
        array(
            'field' => 'last_name',
            'label' => 'Last Name',
            'rules' => 'required|alpha_numeric_spaces',
            'errors' => array(
                'required' => 'Last name is required',
                'alpha_numeric_spaces' => 'Only letter and number allowed'
            ),
        ),
        array(
            'field' => 'email',
            'label' => 'Email Address',
            'rules' => 'required|valid_email'
        ),
        array(
            'field' => 'phone',
            'label' => 'Phone',
            'rules' => 'required|numeric'
        ),
        array(
            'field' => 'date',
            'label' => 'Date',
            'rules' => 'required'
        ),
        array(
            'field' => 'time',
            'label' => 'Time',
            'rules' => 'required'
        ),
        array(
            'field' => 'note',
            'label' => 'Message',
            'rules' => 'alpha_numeric_spaces'
        ),
        array(
            'field' => 'g-recaptcha-response',
            'label' => 'I am not a robot',
            'rules' => 'required'
        ),
    ),
    'page' => array(
        array(
            'field' => 'title',
            'label' => 'Page Title',
            'rules' => 'required'
        ),
        array(
            'field' => 'content',
            'label' => 'Page Content',
            'rules' => 'required'
        )
    ),
    'signup' => array(
        array(
            'field' => 'name',
            'label' => 'Name',
            'rules' => 'required',
        ),
        array(
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'required|valid_email|is_unique[customer .email]',
            'errors' => array(
                'required' => 'You must provide a valid email address.',
                'is_unique' => 'This %s already exists.'
            ),
        ),
        array(
            'field' => 'phone',
            'label' => 'Phone',
            'rules' => 'required'
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'required'
        )
    ),
    'login' => array(
        array(
            'field' => 'email',
            'label' => 'Email',
            'rules' => 'required|valid_email',
        ),
        array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'required'
        )
    ),
);
