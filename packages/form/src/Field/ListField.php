<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Form\Field;

use DOMNode;
use Windwalker\Data\Collection;
use Windwalker\DOM\DOMElement;
use Windwalker\Form\Field\Concern\ListOptionsTrait;
use Windwalker\Utilities\TypeCast;

use function Windwalker\DOM\h;
use function Windwalker\value_compare;

/**
 * The ListField class.
 *
 * @method  $this  size(int $value)
 * @method  mixed  getSize()
 * @method  $this  multiple(bool $value = null)
 * @method  mixed  isMultiple()
 *
 * @since  2.0
 */
class ListField extends AbstractField
{
    use ListOptionsTrait;

    /**
     * @inheritDoc
     */
    public function prepareInput(DOMElement $input): DOMElement
    {
        return $input;
    }

    protected function createInputElement(array $attrs = []): DOMElement
    {
        return h('select', $attrs, null);
    }

    /**
     * buildInput
     *
     * @param  DOMElement  $input
     * @param  array       $options
     *
     * @return DOMElement
     */
    public function buildFieldElement(DOMElement $input, array $options = []): string|DOMElement
    {
        $input = $this->prepareListElement($input);

        if ($this->isMultiple()) {
            $input['name'] = $this->getInputName('[]');
        }

        return $input;
    }

    protected function appendOption(DOMElement $select, DOMElement|array $option, ?string $group = null): void
    {
        if (is_array($option)) {
            $select->appendChild($optGroup = h('optgroup', ['label' => $group]));

            foreach ($option as $opt) {
                $this->appendOption($optGroup, $opt);
            }

            return;
        }

        $option = clone $option;
        $value = $this->getValue();

        if (!$this->isMultiple()) {
            if (value_compare($option['value'], $value, '==')) {
                $option->setAttribute('selected', 'selected');
            }
        } elseif (value_compare($option['value'], $value, 'in')) {
            $option->setAttribute('selected', 'selected');
        }

        $select->appendChild($option);
    }

    /**
     * getValue
     *
     * @return  array|string
     */
    public function getValue(): mixed
    {
        $value = parent::getValue();

        if ($this->isMultiple()) {
            if (is_array($value)) {
                return $value;
            }

            if (is_json($value)) {
                return json_decode($value, true);
            }

            if (is_string($value)) {
                return Collection::explode(',', (string) $value)
                    ->map('trim')
                    ->filter('strlen')
                    ->dump();
            }

            return TypeCast::toArray($value);
        }

        return (string) $value;
    }
}
