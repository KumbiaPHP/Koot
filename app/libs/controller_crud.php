<?php

/**
 * Parent Controller to manage the database with the
 * new ActiveRecord
 */
class ControllerCrud extends AdminController
{

    /** @var string Nombre del modelo en CamelCase */
    public $model = '';

    public function index()
    {
        View::select('page');
        $this->data = $this->model::paginateQuery('SELECT * FROM ' . strtolower($this->model), 1, 3);
    }

    public function page(int $page = 1)
    {
        if($page === 1) {
            Redirect::toAction('index');
            return;
        }

        $this->data = $this->model::paginateQuery('SELECT * FROM ' . strtolower($this->model), $page, 3);
    }

    public function create()
    {
        // It is verified if the data has been sent via POST
        if (Input::hasPost('data')) {
            $obj = new $this->model;
            // Try to save the user
            if ($obj->create(Input::post('data'))) {
                // Success message and return to the list
                Flash::valid(_('Record created '));
                Redirect::toAction('page');
                return;
            }
            // If it fails the data is persistent in the form
            $this->data = Input::post('data');
        }
    }

    public function edit(int $id)
    {
        View::select('create');
        // Load the data from database
        $this->data = $this->model::get($id);
        //If not exist
        if (!$this->data) {
            Flash::warning(_('Record not found'));
            Redirect::toAction('page');
            return;
        }
        // It is verified if the data has been sent via POST
        if (Input::hasPost('data')) {
            // Try to save changes
            if ($this->data->update(Input::post('data'))) {
                // Success message and return to the list
                Flash::valid(_('Record updated'));
                Redirect::toAction('page');
                return;
            }
            // If it fails the data is persistent in the form
            $this->data = Input::post('data');
        }
    }
    
    public function show(int $id)
    {
        $this->data = $this->model::get($id);
    }

    public function delete(int $id)
    {
        if (!$this->model::delete($id)) {
            Flash::error(_('Something was wrong'));
        }
        
        Redirect::toAction('page');
    }
}
