<?php
declare(strict_types=1);

namespace i80586\Form;

/**
 * @author Rasim Ashurov
 * @date 6 April, 2022
 */
class Html
{

    private static self $instance;

    public function __wakeup()
    {
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function instance(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function tag(string $tagName, string|false $content = false, array $options = []): string
    {
        $tag = sprintf('<%s%s>', $tagName, self::generateHtmlOptionsToString($options));
        if ($content !== false) {
            $tag .= sprintf('%s</%s>', $content, $tagName);
        }
        return $tag;
    }

    public function label(string $text, ?string $for = null, array $options = []): string
    {
        if (!empty($for)) {
            $options['for'] = $for;
        }
        return $this->tag('label', $text, $options);
    }

    public function input(string $name, mixed $value, array $options = [], string $type = 'text'): string
    {
        $options['class'] = self::classNameWithError($name, $options['class'] ?? 'form-control');
        $options['value'] = old($name, $value);
        $options['id']    = $name;
        $options['name']  = $name;
        $options['type']  = $type;

        $html = $this->appendLabelIfNeeded($name, $options);
        $html .= sprintf('<input%s>', self::generateHtmlOptionsToString(array_reverse($options)));

        if (!empty($options['errorLabel']) && str_contains($options['class'], 'is-invalid')) {
            $html .= $this->tag('span', $options['errorLabel'], ['class' => 'text-danger']);
        }

        return $html;
    }

    public function dropDown(string $name, mixed $chosen, array $list = [], array $options = []): string
    {
        $optionsList = [];
        if (!empty($options['prompt'])) {
            $optionsList[] = $this->tag('option', $options['prompt']);
            unset($options['prompt']);
        }
        foreach ($list as $value => $key) {
            $htmlOptions = [];
            if ((string)$value !== '') {
                $htmlOptions = ['value' => $value];
            }
            if ($value == old($name, $chosen)) {
                $htmlOptions['selected'] = 'selected';
            }
            $optionsList[] = $this->tag('option', $key, $htmlOptions);
        }
        $options['class'] = self::classNameWithError($name, $options['class'] ?? 'form-control');
        $html = $this->appendLabelIfNeeded($name, $options);
        $html .= $this->tag('select', implode('', $optionsList), array_merge([
            'name' => $name,
            'id'   => $name
        ], $options));
        return $html;
    }

    protected function appendLabelIfNeeded(string $name, array &$options): string
    {
        $html = '';
        if (!isset($options['label'])) {
            $html .= $this->label(ucfirst($name), $name);
        } elseif ($options['label'] !== false) {
            $html .= $this->label($options['label'], $name);
        }
        unset($options['label']);
        return $html;
    }

    protected static function classNameWithError(string $fieldName, string $classNames): string
    {
        $errors = \View::getShared()['errors'] ?? [];
        if ($errors->has($fieldName)) {
            $classNames .= ' is-invalid';
        }
        return $classNames;
    }

    protected static function generateHtmlOptionsToString(array $options): string
    {
        $htmlOptionsAsString = join(' ', array_map(function ($key) use ($options) {
            if (is_bool($options[$key])) {
                return $options[$key] ? $key : '';
            }
            return $key . '="' . $options[$key] . '"';
        }, array_keys($options)));
        if (!empty($htmlOptionsAsString)) {
            $htmlOptionsAsString = ' ' . $htmlOptionsAsString;
        }
        return $htmlOptionsAsString;
    }

}
