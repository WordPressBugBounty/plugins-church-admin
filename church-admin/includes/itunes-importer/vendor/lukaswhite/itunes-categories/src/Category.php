<?php


namespace Lukaswhite\ItunesCategories;


use phpDocumentor\Reflection\Types\Boolean;

class Category
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Category
     */
    protected $parent;

    /**
     * @var array
     */
    protected $children = [];

    /**
     * Category constructor.
     *
     * @param string $key
     * @param string $name
     * @param Category $parent
     */
    public function __construct(string $key, string $name, Category $parent = null)
    {
        $this->key = $key;
        $this->name = $name;
        $this->parent = $parent;
    }

    /**
     * Create an instance from an array
     *
     * @param string $key
     * @param array $data
     * @return static
     */
    public static function create(string $key, array $data): self
    {
        $category = new self($key, $data['label']);
        if (isset($data['children'])) {
            foreach($data['children'] as $childKey => $child) {
                $category->addChild(new Category($childKey, $child['label'],$category));
            }
        }
        return $category;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return !! $this->parent;
    }

    /**
     * @return Category|null
     */
    public function getParent(): ?Category
    {
        return $this->parent;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param string $key
     * @return Category|null
     */
    public function getChild(string $key): ?Category
    {
        return $this->hasChild($key) ? $this->children[$key] : null;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return !! count($this->children);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasChild(string $key): bool
    {
        return $this->hasChildren() && isset($this->children[$key]);
    }

    /**
     * @param Category $category
     */
    public function addChild(Category $category)
    {
        $this->children[$category->getKey()] = $category;
    }

}