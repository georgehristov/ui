<?php

declare(strict_types=1);

namespace atk4\ui\Form\Control;

use atk4\ui\Exception;

/**
 * Class Upload.
 */
class Upload extends Input
{
    public $inputType = 'hidden';
    /**
     * The action button to open file browser dialog.
     *
     * @var View
     */
    public $action;

    /**
     * The uploaded file id.
     * This id is return on form submit.
     * If not set, will default to file name.
     * file id is also sent with onDelete Callback.
     *
     * @var string
     */
    public $fileId;

    /**
     * Whether you need to open file browser dialog using input focus or not.
     * default to true.
     *
     * @var bool
     * @obsolete
     * hasFocusEnable has been disable in js plugin and this property will be removed.
     * Upload field is only using click handler now.
     */
    public $hasFocusEnable = false;

    /**
     * The input default template.
     *
     * @var string
     */
    public $defaultTemplate = 'formfield/upload.html';

    /**
     * Callback is use for onUpload or onDelete.
     *
     * @var \atk4\ui\JsCallback
     */
    public $cb;

    /**
     * Allow multiple file or not.
     * CURRENTLY NOT SUPPORTED.
     *
     * @var bool
     */
    public $multiple = false;

    /**
     * An array of string value for accept file type.
     * ex: ['.jpg', '.jpeg', '.png'] or ['images/*'].
     *
     * @var array
     */
    public $accept = [];

    /**
     * Whether cb has been define or not.
     *
     * @var bool
     */
    public $hasUploadCb = false;
    public $hasDeleteCb = false;

    public $jsActions = [];

    /** @var bool check if callback is trigger by one of the action. */
    private $_isCbRunning = false;

    public function init(): void
    {
        parent::init();

        //$this->inputType = 'hidden';

        $this->cb = \atk4\ui\JsCallback::addTo($this);

        if (!$this->action) {
            $this->action = new \atk4\ui\Button(['icon' => 'upload', 'disabled' => ($this->disabled || $this->readonly)]);
        }
    }

    /**
     * Allow to set file id and file name
     *  - fileId will be the file id sent with onDelete callback.
     *  - fileName is the field value display to user.
     *
     * @param string      $fileId   // Field id for onDelete Callback
     * @param string|null $fileName // Field name display to user
     * @param mixed       $junk
     *
     * @return $this|void
     */
    public function set($fileId = null, $fileName = null, $junk = null)
    {
        $this->setFileId($fileId);

        if (!$fileName) {
            $fileName = $fileId;
        }

        return $this->setInput($fileName, $junk);
    }

    /**
     * Set input field value.
     *
     * @param mixed $value the field input value
     *
     * @return $this
     */
    public function setInput($value, $junk = null)
    {
        return parent::set($value, $junk);
    }

    /**
     * Get input field value.
     *
     * @return array|false|mixed|string|null
     */
    public function getInputValue()
    {
        return $this->field ? $this->field->get() : $this->content;
    }

    /**
     * Set file id.
     */
    public function setFileId($id)
    {
        $this->fileId = $id;
    }

    /**
     * Add a js action to be return to server on callback.
     */
    public function addJsAction($action)
    {
        if (is_array($action)) {
            $this->jsActions = array_merge($action, $this->jsActions);
        } else {
            $this->jsActions[] = $action;
        }
    }

    /**
     * onDelete callback.
     * Call when user is removing an already upload file.
     *
     * @param callable $fx
     */
    public function onDelete($fx = null)
    {
        if (is_callable($fx)) {
            $this->hasDeleteCb = true;
            $action = $_POST['action'] ?? null;
            if ($this->cb->triggered() && $action === 'delete') {
                $this->_isCbRunning = true;
                $fileName = $_POST['f_name'] ?? null;
                $this->cb->set(function () use ($fx, $fileName) {
                    $this->addJsAction(call_user_func_array($fx, [$fileName]));

                    return $this->jsActions;
                });
            }
        }
    }

    /**
     * onUpload callback.
     * Call when user is uploading a file.
     *
     * @param callable $fx
     */
    public function onUpload($fx = null)
    {
        if (is_callable($fx)) {
            $this->hasUploadCb = true;
            if ($this->cb->triggered()) {
                $this->_isCbRunning = true;
                $action = $_POST['action'] ?? null;
                $files = $_FILES ?? null;
                if ($files) {
                    //set fileId to file name as default.
                    $this->fileId = $files['file']['name'];
                    // display file name to user as default.
                    $this->setInput($this->fileId);
                }
                if ($action === 'upload' && !$files['file']['error']) {
                    $this->cb->set(function () use ($fx, $files) {
                        $this->addJsAction(call_user_func_array($fx, $files));
                        //$value = $this->field ? $this->field->get() : $this->content;
                        $this->addJsAction([
                            $this->js()->atkFileUpload('updateField', [$this->fileId, $this->getInputValue()]),
                        ]);

                        return $this->jsActions;
                    });
                } elseif ($action === null || isset($files['file']['error'])) {
                    $this->cb->set(function () use ($fx, $files) {
                        return call_user_func($fx, 'error');
                    });
                }
            }
        }
    }

    /**
     * Rendering view.
     */
    public function renderView()
    {
        //need before parent rendering.
        if ($this->disabled) {
            $this->addClass('disabled');
        }
        parent::renderView();

        if (!$this->_isCbRunning && (!$this->hasUploadCb || !$this->hasDeleteCb)) {
            throw new Exception('onUpload and onDelete callback must be called to use file upload. Missing one or both of them.');
        }
        if (!empty($this->accept)) {
            $this->template->trySet('accept', implode(',', $this->accept));
        }
        if ($this->multiple) {
            //$this->template->trySet('multiple', 'multiple');
        }

        if ($this->placeholder) {
            $this->template->trySet('PlaceHolder', $this->placeholder);
        }

        //$value = $this->field ? $this->field->get() : $this->content;
        $this->js(true)->atkFileUpload([
            'uri' => $this->cb->getJsUrl(),
            'action' => $this->action->name,
            'file' => ['id' => $this->fileId ?: $this->field->get(), 'name' => $this->getInputValue()],
            'hasFocus' => $this->hasFocusEnable,
            'submit' => ($this->form->buttonSave) ? $this->form->buttonSave->name : null,
        ]);
    }
}