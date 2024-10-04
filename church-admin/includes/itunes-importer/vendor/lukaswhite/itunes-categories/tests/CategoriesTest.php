<?php

use Lukaswhite\ItunesCategories\Categories;
use Lukaswhite\ItunesCategories\Category;

class CategoriesTest extends \PHPUnit\Framework\TestCase
{

    public function test_can_get_all_categories()
    {
        $categories = new Categories();
        $this->assertTrue(is_array($categories->all()));

    }

    public function test_can_get_all_categories_as_array()
    {
        $categories = new Categories();
        $this->assertTrue(is_array($categories->arr()));
        $this->assertArrayHasKey('Kids &amp; Family', $categories->arr());
        $this->assertTrue(is_array($categories->arr()['Kids &amp; Family']));
        $this->assertTrue(is_array($categories->arr()['Kids &amp; Family']));
        $this->assertArrayHasKey('children', $categories->arr()['Kids &amp; Family']);
        $this->assertTrue(is_array($categories->arr()['Kids &amp; Family']['children']));
        $this->assertArrayHasKey('Pets &amp; Animals', $categories->arr()['Kids &amp; Family']['children']);
    }

    public function test_can_get_categories()
    {
        $categories = new Categories();
        $kids = $categories->get('Kids &amp; Family');
        $this->assertInstanceOf(Category::class,$kids);
        $this->assertEquals('Kids &amp; Family', $kids->getKey());
        $this->assertEquals('Kids & Family', $kids->getName());
        $this->assertFalse($kids->hasParent());
        $this->assertNull($kids->getParent());
        $this->assertTrue($kids->hasChildren());
        $this->assertTrue(is_array($kids->getChildren()));
        $this->assertEquals(4,count($kids->getChildren()));
        $this->assertTrue($kids->hasChild('Pets &amp; Animals'));
        $pets = $kids->getChild('Pets &amp; Animals');
        $this->assertInstanceOf(Category::class,$pets);
        $this->assertEquals('Pets &amp; Animals', $pets->getKey());
        $this->assertEquals('Pets & Animals', $pets->getName());
        $this->assertTrue($pets->hasParent());
        $this->assertEquals($kids->getKey(),$pets->getParent()->getKey());
    }

    public function test_get_child_returns_null_if_it_does_not_exist()
    {
        $categories = new Categories();
        $sport = $categories->get('Sports');
        $this->assertNull($sport->getChild('Quidditch'));
    }

    public function test_can_get_subcategories()
    {
        $categories = new Categories();
        $pets = $categories->get('Kids &amp; Family', 'Pets &amp; Animals');
        $this->assertInstanceOf(Category::class,$pets);
        $this->assertEquals('Pets &amp; Animals', $pets->getKey());
        $this->assertEquals('Pets & Animals', $pets->getName());
    }

    public function test_get_returns_null_for_invalid_categories()
    {
        $categories = new Categories();
        $this->assertNull($categories->get('Witchcraft'));
    }

    public function test_get_returns_null_for_invalid_sub_categories()
    {
        $categories = new Categories();
        $this->assertNull($categories->get('Sports', 'Quidditch'));
    }

    public function test_get_returns_false_for_invalid_categories_and_sub_categories()
    {
        $categories = new Categories();
        $this->assertNull($categories->get('Countries', 'Estonia'));
    }

    public function test_can_check_categories_exist()
    {
        $categories = new Categories();
        $this->assertTrue($categories->has('Sports' ));
        $this->assertTrue($categories->has('Sports', 'Football'));
        $this->assertFalse($categories->has('Witchcraft' ));
        $this->assertFalse($categories->has('Sports', 'Quidditch'));
    }

    public function test_can_serialize_to_json()
    {
        $categories = new Categories();
        $this->assertEquals($categories->arr(),json_decode(json_encode($categories),true));
    }

}