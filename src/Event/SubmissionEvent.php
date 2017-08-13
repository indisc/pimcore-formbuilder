<?php

namespace FormBuilderBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SubmissionEvent extends Event
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $formConfiguration;

    /**
     * @var array
     */
    private $form;

    /**
     * @param Request       $request
     * @param array         $formConfiguration
     * @param FormInterface $form
     */
    public function __construct(Request $request, $formConfiguration = [], FormInterface $form)
    {
        $this->request = $request;
        $this->formConfiguration = $formConfiguration;
        $this->form = $form;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getFormConfiguration()
    {
        return $this->formConfiguration;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

}