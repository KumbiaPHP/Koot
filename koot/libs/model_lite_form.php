<?php

/**
 * Class ModelLiteForm
 *
 * Utility class to generate HTML forms from LiteRecord models.
 *
 * This implementation follows clean architecture principles by isolating concerns:
 * - Form rendering is separated into small, reusable helper methods.
 * - Repetitive tasks (e.g. HTML escaping, attribute string building) are centralized.
 * - The overall logic remains simple (KISS) and avoids code duplication (DRY).
 *
 * @package App\Libs
 */
class ModelLiteForm
{
    /**
     * List of number types rendered as input fields.
     *
     * @var array
     */
    protected static array $numberTypes = [
        'tinyint',
        'smallint',
        'mediumint',
        'int',
        'integer',
        'bigint',
        'float',
        'double',
        'precision',
        'real',
        'decimal',
        'numeric',
        'year',
        'day',
        'int unsigned',
    ];

    /**
     * List of text types rendered as textarea fields.
     *
     * @var array
     */
    protected static array $textTypes = [
        'text',
        'mediumtext',
        'longtext',
        'blob',
        'mediumblob',
        'longblob',
    ];

    /**
     * List of date types rendered as date input fields.
     *
     * @var array
     */
    protected static array $dateTypes = ['date'];

    /**
     * List of datetime types rendered as datetime-local input fields.
     *
     * @var array
     */
    protected static array $dateTimeTypes = ['datetime', 'timestamp'];

    /**
     * Escapes a string for safe HTML output.
     *
     * @param string $value The string to escape.
     * @return string The escaped string.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Retrieves the model's value for a given field.
     *
     * @param object $model The model instance.
     * @param string $field The field name.
     * @return string The escaped field value, or an empty string if not set.
     */
    private static function getModelValue(object $model, string $field): string
    {
        return isset($model->$field) ? self::escape((string)$model->$field) : '';
    }

    /**
     * Generates the HTML for the complete form based on the model.
     *
     * @param LiteRecord $model The model instance.
     * @param string $action The form action URL.
     * @return string The generated HTML form.
     */
    public static function create(LiteRecord $model, string $action = ''): string
    {
        $modelName = get_class($model);
        if ('' === $action) {
            $action = ltrim(Router::get('route'), '/');
        }
        $html = '';

        // Start form
        $html .= sprintf(
            '<form action="%s%s" method="post" id="%s" class="scaffold">%s',
            PUBLIC_PATH,
            self::escape($action),
            self::escape($modelName),
            PHP_EOL
        );

        // Primary key field (hidden)
        $pk = $modelName::getPK();
        $pkValue = self::getModelValue($model, $pk);
        if ($pkValue !== '') {
            $html .= sprintf(
                '<input id="%s_%s" name="%s[%s]" value="%s" type="hidden">%s',
                $modelName, $pk, $modelName, $pk, $pkValue, PHP_EOL
            );
        }

        // Retrieve fields metadata and remove the primary key field
        $fields = $modelName::metadata()->getFields();
        unset($fields[$pk]);

        // Generate HTML for each field
        foreach ($fields as $field => $meta) {
            $html .= self::generateFieldHtml($model, $field, $meta, $modelName);
        }

        // Submit button and close form
        $html .= sprintf('<input type="submit" />%s', PHP_EOL);
        $html .= sprintf('</form>%s', PHP_EOL);

        return $html;
    }

    /**
     * Generates the HTML for a single form field (label and input).
     *
     * @param LiteRecord $model The model instance.
     * @param string $field The field name.
     * @param array $meta The field metadata.
     * @param string $modelName The model class name.
     * @return string The generated field HTML.
     */
    private static function generateFieldHtml(LiteRecord $model, string $field, array $meta, string $modelName): string
    {
        $alias = self::getFieldAlias($field);
        $inputId = $modelName . '_' . $field;
        $inputName = sprintf('%s[%s]', $modelName, $field);
        $isRequired = !$meta['Null'];
        $requiredAttr = $isRequired ? 'required' : '';
        $labelClass = $isRequired ? ' class="required"' : '';
        $asterisk = $isRequired ? ' *' : '';

        $html = sprintf('<label%s>%s%s%s', $labelClass, $alias, $asterisk, PHP_EOL);

        // If the field ends with '_id', use a database select
        if (str_ends_with($field, '_id')) {
            $fieldValue = $model->$field ?? '';
            $html .= Form::dbSelect("{$modelName}.{$field}", null, null, 'Select', '', $fieldValue) . PHP_EOL;
            $html .= '</label>' . PHP_EOL;
            return $html;
        }

        // Generate input based on field type
        $value = self::getModelValue($model, $field);
        $html .= self::generateInputHtml($meta['Type'], $value, $inputId, $inputName, $requiredAttr) . PHP_EOL;
        $html .= '</label>' . PHP_EOL;

        return $html;
    }

    /**
     * Converts a field name into a human-readable alias.
     *
     * @param string $name The original field name.
     * @return string The alias.
     */
    private static function getFieldAlias(string $name): string
    {
        return ucwords(str_replace('_', ' ', $name));
    }

    /**
     * Generates the HTML for an input element based on the field type.
     *
     * @param string $type The database field type.
     * @param string $value The field value.
     * @param string $inputId The id attribute for the input element.
     * @param string $inputName The name attribute for the input element.
     * @param string $requiredAttr The required attribute (if any).
     * @return string The generated input HTML.
     */
    private static function generateInputHtml(
        string $type,
        string $value,
        string $inputId,
        string $inputName,
        string $requiredAttr
    ): string {
        if (in_array($type, self::$numberTypes, true)) {
            $input = self::buildInputElement('number', $inputId, $inputName, $value, $requiredAttr);
        } elseif (in_array($type, self::$dateTypes, true)) {
            $input = self::buildInputElement('date', $inputId, $inputName, $value, $requiredAttr);
        } elseif (in_array($type, self::$dateTimeTypes, true)) {
            $input = self::buildInputElement('datetime-local', $inputId, $inputName, $value, $requiredAttr);
        } elseif (self::isSelectTypeMatched($type)) {
            $input = self::buildSelectElement($inputId, $inputName, self::getEnumOptions($type), $value, $requiredAttr);
        } elseif (in_array($type, self::$textTypes, true)) {
            $input = self::buildTextareaElement($inputId, $inputName, $requiredAttr, $value);
        } else {
            $input = self::buildInputElement('text', $inputId, $inputName, $value, $requiredAttr);
        }

        return $input;
    }

    /**
     * Checks whether the given field type should be rendered as a select element.
     *
     * @param string $type The field type.
     * @return bool True if the type is enum, set, or bool.
     */
    private static function isSelectTypeMatched(string $type): bool
    {
        return str_starts_with($type, 'enum') ||
               str_starts_with($type, 'set') ||
               str_starts_with($type, 'bool');
    }

    /**
     * Builds an HTML input element.
     *
     * @param string $type The input type.
     * @param string $id The id attribute.
     * @param string $name The name attribute.
     * @param string $value The value attribute.
     * @param string $requiredAttr The required attribute (if applicable).
     * @return string The HTML input element.
     */
    private static function buildInputElement(
        string $type,
        string $id,
        string $name,
        string $value = '',
        string $requiredAttr = ''
    ): string {
        $attributes = [
            'id'    => $id,
            'name'  => $name,
            'type'  => $type,
            'value' => $value,
        ];
        if ($requiredAttr !== '') {
            $attributes[$requiredAttr] = $requiredAttr;
        }
        return sprintf('<input %s>', self::attributesToString($attributes));
    }

    /**
     * Builds an HTML textarea element.
     *
     * @param string $id The id attribute.
     * @param string $name The name attribute.
     * @param string $requiredAttr The required attribute (if applicable).
     * @param string $content The textarea content.
     * @return string The HTML textarea element.
     */
    private static function buildTextareaElement(
        string $id,
        string $name,
        string $requiredAttr = '',
        string $content = ''
    ): string {
        $attributes = [
            'id'   => $id,
            'name' => $name,
        ];
        if ($requiredAttr !== '') {
            $attributes[$requiredAttr] = $requiredAttr;
        }
        return sprintf('<textarea %s>%s</textarea>', self::attributesToString($attributes), $content);
    }

    /**
     * Builds an HTML select element.
     *
     * @param string $id The id attribute.
     * @param string $name The name attribute.
     * @param array $options The options for the select element.
     * @param string $selectedValue The currently selected value.
     * @param string $requiredAttr The required attribute (if applicable).
     * @return string The HTML select element.
     */
    private static function buildSelectElement(
        string $id,
        string $name,
        array $options,
        string $selectedValue,
        string $requiredAttr
    ): string {
        $attributes = [
            'id'   => $id,
            'name' => $name,
        ];
        if ($requiredAttr !== '') {
            $attributes[$requiredAttr] = $requiredAttr;
        }
        $html = sprintf('<select %s>', self::attributesToString($attributes)) . PHP_EOL;
        foreach ($options as $option) {
            $optionEscaped = self::escape($option);
            $selected = ($option === $selectedValue) ? ' selected' : '';
            $html .= sprintf('<option value="%s"%s>%s</option>', $optionEscaped, $selected, $optionEscaped) . PHP_EOL;
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Converts an associative array of attributes into an HTML attributes string.
     *
     * @param array $attributes The attributes to convert.
     * @return string The resulting attributes string.
     */
    private static function attributesToString(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $key => $value) {
            if ($value === '' || is_int($key)) {
                continue;
            }
            $parts[] = sprintf('%s="%s"', $key, self::escape($value));
        }
        return implode(' ', $parts);
    }

    /**
     * Extracts options from an enum or set type definition.
     *
     * @param string $typeDefinition The type definition string from the database.
     * @return array The list of options.
     */
    private static function getEnumOptions(string $typeDefinition): array
    {
        if (preg_match('/^(enum|set)\((.*)\)$/', $typeDefinition, $matches)) {
            return str_getcsv($matches[2], ',', "'");
        }
        return [];
    }
}
