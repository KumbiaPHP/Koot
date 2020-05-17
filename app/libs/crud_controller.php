<?php

/**
 * Parent Controller to manage the database with the
 * new ActiveRecord
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
        if (Input::hasPost('data')) {
            $obj = new Users;
            // Try to save the user
            if ($obj->create(Input::post('data'))) {
                // Success message and return to the list
                Flash::valid(_('Record created '));
                return Redirect::toAction('page');
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
            return Redirect::toAction('page');
        }
        // It is verified if the data has been sent via POST
        if (Input::hasPost('data')) {
            // Try to save changes
            if ($this->data->update(Input::post('data'))) {
                // Success message and return to the list
                Flash::valid(_('Record updated'));
                return Redirect::toAction('page');
            }
            // If it fails the data is persistent in the form
            $this->data = Input::post('data');
        }
    }
}
