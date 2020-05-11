<?php

/**
 * Parent Controller to manage the database
 */
class CrudController extends AdminController
{
    
    /** @var string Nombre del modelo en CamelCase */
    public $model = '';

    public function index()
    {
        Redirect::toAction('page');
    }

    public function page(int $page = 1)
    {
        $this->data = $this->model::paginateQuery('SELECT * FROM ' . strtolower($this->model), $page, 30);
    }

    public function create()
    {
        // It is verified if the data has been sent via POST
        if (Input::hasPost($this->model)) {
            $obj = new Users;
            // Try to save the user
            if ($obj->create(Input::post($this->model))) {
                // Success message and return to the list
                Flash::valid(_('Record created '));
                return Redirect::to();
            }
            // If it fails the data is persistent in the form
            $this->data = Input::post($this->model);
        }
    }

    public function edit(int $id)
    {
        // Load the data from database
        $this->data = $this->model::get($id);
        // It is verified if the data has been sent via POST
        if (Input::hasPost($this->model)) {
            // Try to save changes
            if ($this->data->update(Input::post($this->model))) {
                // Success message and return to the list
                Flash::valid(_('Record updated'));
                return Redirect::to();
            }
            // If it fails the data is persistent in the form
            $this->data = Input::post($this->model);
        }
    }
}
