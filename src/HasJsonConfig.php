<?php

namespace RussellRamey\JsonConfig;

use Illuminate\Database\Eloquent\Model;

trait HasJsonConfig
{
    protected string $jsonConfigAttribute = 'config';

    protected bool $jsonAttributesExpanded = false;

    protected static function bootHasJsonConfig(): void
    {
        static::retrieved(function (Model $model) {
            $model->expandJsonSchemaAttributes();
        });

        static::saving(function (Model $model) {
            $model->compactJsonSchemaAttributes();
        });

        static::saved(function (Model $model) {
            $model->expandJsonSchemaAttributes();
        });
    }

    protected function initializeHasJsonConfig(): void
    {
        $attributeName = $this->getJsonSchemaAttributeName();
        if (!array_key_exists($attributeName, $this->casts)) {
            $this->casts[$attributeName] = 'array';
        }
    }

    protected function getJsonSchemaAttributeName(): string
    {
        return $this->jsonConfigAttribute;
    }

    protected function getJsonSchemaAttributeNames(): array
    {
        return $this->jsonConfigAttributes ?? [];
    }

    protected function shouldStoreInJsonSchema(string $key): bool
    {
        return in_array($key, $this->getJsonSchemaAttributeNames(), true);
    }

    protected function expandJsonSchemaAttributes(): void
    {
        if ($this->jsonAttributesExpanded) {
            return;
        }

        $jsonAttributeName = $this->getJsonSchemaAttributeName();
        $jsonData = $this->getAttributeValue($jsonAttributeName);

        if (is_array($jsonData) && !empty($jsonData)) {
            foreach ($jsonData as $key => $value) {
                if ($this->shouldStoreInJsonSchema($key)) {
                    $this->attributes[$key] = $value;
                }
            }
        }

        $this->jsonAttributesExpanded = true;
    }

    protected function compactJsonSchemaAttributes(): void
    {
        $jsonAttributeName = $this->getJsonSchemaAttributeName();
        $jsonSchemaAttributeNames = $this->getJsonSchemaAttributeNames();

        if (empty($jsonSchemaAttributeNames)) {
            return;
        }

        $jsonData = $this->getAttributeValue($jsonAttributeName) ?? [];

        if (!is_array($jsonData)) {
            $jsonData = [];
        }

        foreach ($jsonSchemaAttributeNames as $key) {
            if (array_key_exists($key, $this->attributes)) {
                $jsonData[$key] = $this->attributes[$key];
                unset($this->attributes[$key]);
            }
        }

        parent::setAttribute($jsonAttributeName, $jsonData);

        $this->jsonAttributesExpanded = false;
    }

    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return parent::getAttribute($key);
        }

        if ($this->shouldStoreInJsonSchema($key)) {
            $jsonAttributeName = $this->getJsonSchemaAttributeName();
            $jsonData = $this->getAttributeValue($jsonAttributeName);

            if (is_array($jsonData) && array_key_exists($key, $jsonData)) {
                return $jsonData[$key];
            }
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        $jsonAttributeName = $this->getJsonSchemaAttributeName();

        if ($key === $jsonAttributeName) {
            $this->jsonAttributesExpanded = false;
            return parent::setAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $jsonAttributeName = $this->getJsonSchemaAttributeName();
        $jsonData = $this->getAttributeValue($jsonAttributeName);

        if (is_array($jsonData)) {
            foreach ($jsonData as $key => $value) {
                if ($this->shouldStoreInJsonSchema($key) && !array_key_exists($key, $array)) {
                    $array[$key] = $value;
                }
            }
        }

        return $array;
    }

    public function hasJsonSchemaAttribute(string $key): bool
    {
        $jsonAttributeName = $this->getJsonSchemaAttributeName();
        $jsonData = $this->getAttributeValue($jsonAttributeName);

        return is_array($jsonData) && array_key_exists($key, $jsonData);
    }

    public function getConfigAttributes(): array
    {
        $jsonAttributeName = $this->getJsonSchemaAttributeName();
        $jsonData = $this->getAttributeValue($jsonAttributeName);

        return is_array($jsonData) ? $jsonData : [];
    }

    public function setJsonConfig(string $key, $value): self
    {
        if (!$this->shouldStoreInJsonSchema($key)) {
            throw new \InvalidArgumentException("Attribute '{$key}' is not defined in jsonConfigAttributes.");
        }

        $this->setAttribute($key, $value);

        return $this;
    }

    public function removeJsonConfig(string $key): self
    {
        $jsonAttributeName = $this->getJsonSchemaAttributeName();
        $jsonData = $this->getAttributeValue($jsonAttributeName);

        if (is_array($jsonData) && array_key_exists($key, $jsonData)) {
            unset($jsonData[$key]);
            $this->setAttribute($jsonAttributeName, $jsonData);
        }

        if (array_key_exists($key, $this->attributes)) {
            unset($this->attributes[$key]);
        }

        return $this;
    }

    public function refresh()
    {
        $this->jsonAttributesExpanded = false;
        return parent::refresh();
    }

    /**
     * Get the fillable attributes for the model.
     * This overrides Laravel's getFillable() method to include JSON schema attributes
     */
    public function getFillable(): array
    {
        $fillable = parent::getFillable();
        $jsonSchemaAttributes = $this->getJsonSchemaAttributeNames();
        
        return array_unique(array_merge($fillable, $jsonSchemaAttributes));
    }
}
