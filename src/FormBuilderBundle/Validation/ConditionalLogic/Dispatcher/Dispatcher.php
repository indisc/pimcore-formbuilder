<?php

namespace FormBuilderBundle\Validation\ConditionalLogic\Dispatcher;

use FormBuilderBundle\Registry\DispatcherRegistry;
use FormBuilderBundle\Storage\FormFieldInterface;
use FormBuilderBundle\Validation\ConditionalLogic\Processor\ConditionalLogicProcessor;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Dispatcher
{
    /**
     * @var ConditionalLogicProcessor
     */
    protected $conditionalLogicProcessor;

    /**
     * @var OptionsResolver
     */
    protected $dispatcherOptions = NULL;

    /**
     * @var DispatcherRegistry
     */
    protected $dispatcherRegistry;

    /**
     * @var array
     */
    protected $optionsResolver = [];

    /**
     * ConstraintConnector constructor.
     *
     * @param ConditionalLogicProcessor $conditionalLogicProcessor
     * @param DispatcherRegistry        $dispatcherRegistry
     */
    public function __construct(DispatcherRegistry $dispatcherRegistry, ConditionalLogicProcessor $conditionalLogicProcessor)
    {
        $this->dispatcherRegistry = $dispatcherRegistry;
        $this->conditionalLogicProcessor = $conditionalLogicProcessor;
        $this->dispatcherOptions = new OptionsResolver();
        $this->dispatcherOptions->setDefaults([
            'formData'         => [],
            'conditionalLogic' => []
        ]);

        $this->dispatcherOptions->setRequired(['formData', 'conditionalLogic']);

    }

    /**
     * @param $dispatcherModule
     * @param $options
     * @param $moduleOptions
     *
     * @return mixed
     */
    public function runFieldDispatcher($dispatcherModule, $options, $moduleOptions = [])
    {
        $this->dispatcherOptions->setDefaults(['field' => NULL]);
        $this->dispatcherOptions->setRequired(['field']);
        $this->dispatcherOptions->setAllowedTypes('field', FormFieldInterface::class);
        $this->dispatcherOptions->resolve($options);

        $conditionActions = $this->conditionalLogicProcessor->process($options['formData'], $options['conditionalLogic'], $options['field']);
        $moduleOptions['appliedConditions'] = $conditionActions;

        return $this->run($dispatcherModule, $options, $moduleOptions);
    }

    /**
     * @param $dispatcherModule
     * @param $options
     * @param $moduleOptions
     *
     * @return mixed
     */
    public function runFormDispatcher($dispatcherModule, $options, $moduleOptions = [])
    {
        $this->dispatcherOptions->resolve($options);

        $conditionActions = $this->conditionalLogicProcessor->process($options['formData'], $options['conditionalLogic']);
        $moduleOptions['appliedConditions'] = $conditionActions;

        return $this->run($dispatcherModule, $options, $moduleOptions);
    }

    /**
     * @param       $dispatcherModule
     * @param       $options
     * @param array $moduleOptions
     * @return mixed
     */
    private function run($dispatcherModule, $options, $moduleOptions)
    {
        if (isset($this->optionsResolver[$dispatcherModule])) {
            $optionsResolver = $this->optionsResolver[$dispatcherModule];
        } else {
            $optionsResolver = new OptionsResolver();
            $this->optionsResolver[$dispatcherModule] = $optionsResolver;
        }

        $dispatcherModuleClass = $this->dispatcherRegistry->get($dispatcherModule);
        $dispatcherModuleClass->configureOptions($optionsResolver);

        //pass available dispatcher option to module if available
        foreach ($optionsResolver->getDefinedOptions() as $optionName) {
            if (isset($options[$optionName])) {
                $moduleOptions[$optionName] = $options[$optionName];
            }
        }

        $moduleOptions = $optionsResolver->resolve($moduleOptions);
        return $dispatcherModuleClass->apply($moduleOptions);
    }
}