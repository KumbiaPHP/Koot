<?php

/**
 * Parent Controller to manage the database with the
 * new ActiveRecord (https://github.com/KumbiaPHP/ActiveRecord)
 */
abstract class ControllerScaffoldLite extends ControllerAdmin
{

    /** Folder in views/_shared/scaffolds/ */
    public $scaffold = 'lite';
    /** Model Name in CamelCase */
    public string $model = '';
    /** Number of records per page */
    public int $perPage = 30;

    public function index()
    {
        View::select('page');
        $this->data = $this->model::paginateQuery('SELECT * FROM ' . strtolower($this->model), 1, $this->perPage);
    }

    public function page(int $page = 1)
    {
        if ($page === 1) {
            Redirect::toAction('');
            return;
        }

        $this->data = $this->model::paginateQuery('SELECT * FROM ' . strtolower($this->model), $page, $this->perPage);
    }

    public function create()
    {
        // It is verified if the data has been sent via POST
        if (Input::hasPost($this->model)) {
            $obj = new $this->model;
            // Try to save the user
            if (!$obj->create(Input::post($this->model))) {
                Flash::error(_('Something was wrong'));
                // If it fails the data is persistent in the form
                $this->{$this->model} = $obj;
                return;
            }

            // Success message and return to the list
            Flash::valid(_('Record created'));
            Redirect::toAction('');
            return;
        }

        $this->{$this->model} = (new $this->model);
    }

    public function edit(int $id)
    {
        // Load the data from database
        $this->{$this->model} = $this->model::get($id);
        //If not exist
        if (!$this->{$this->model}) {
            Flash::warning(_('Record not found'));
            Redirect::toAction('');
            return;
        }

        View::select('create');

        // It is verified if the data has been sent via POST
        if (Input::hasPost($this->model)) {
            // Try to save changes
            if (!$this->{$this->model}->update(Input::post($this->model))) {
                Flash::error(_('Something was wrong'));
                return;
            }

            // Success message and return to the list
            Flash::valid(_('Record updated'));
            Redirect::toAction('');
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

        Redirect::toAction('');
    }
}
