# Data Binding

As well as creating data, a common use case for forms is to update existing data. This is where data binding comes in.
The basic idea is that you pass the form a data object or array, and it will populate the form with the data and in the case
of an object, update the object with the new values when the form is submitted.

## Object Data Mapping

The [default object data mapper](/src/DataMapper/ObjectDataMapper.php) prioritizes getters (and issers) and setters for retrieving
and setting property values, respectively. If no getter or setter is found, the mapper will attempt to access the property
directly (if it is public).

```php
class Person {
    public string $name = 'Bob Smith';
    public int $age = 42;
    private array $interests = [];
    private bool $active = true;

    public function getInterests(): array
    {
        // This method will be called when the form is rendered to get the 'interests' property
        return $this->interests;
    }

    public function setInterests(array $interests): void
    {
        // This method will be called when the form is submitted to set the 'interests' property
        $this->interests = $interests;
    }

    public function setActive($active): void
    {
        // This method will be called when the form is submitted to set the 'active' property
        $this->active = (bool)$active;
    }

    public function isActive(): bool
    {
       // This method will be called when the form is rendered to get the 'active' property
       return $this->active;
    }
}
```

## Building the Form

Now we have our model set up, it's time to create the form:

```php
$person = new Person();

$builder = new \Palmtree\Form\FormBuilder('some_form_key', $person);

$builder
    ->add('name', 'text')
    ->add('age', 'number')
    ->add('interests', 'choice', [
        'choices' => [
            'football' => 'Football',
            'tennis'   => 'Tennis',
            'golf'     => 'Golf',
            'cricket'  => 'Cricket',
        ],
    ])
    ->add('active', 'checkbox', [
        'required' => false,
    ])
;
```

> Note: You may also use a regular stdClass for two-way data binding using the default object data mapper.

For more advanced use cases you may override the default data mapper by implementing the [`DataMapperInterface`](/src/DataMapper/DataMapperInterface.php):

```php
$builder->setDataMapper(new SomeOtherDataMapper());
```

When the form is submitted and validated, the data object will be updated with the new values.

## Array Data Mapping

You may use an array to populate the form instead of an object. In this case, the array keys will be used as the property
names.

```php
$data = [
    'name'       => 'Bob Smith',
    'age'        => 42,
    'interests'  => ['football', 'tennis'],
    'active'     => true,
];

$builder = new \Palmtree\Form\FormBuilder('some_form_key', $data);
```

When using an array to populate the form the array will not be updated when the form is submitted because it
is passed by value. If you want two-way data binding you should use an [ArrayObject](https://www.php.net/manual/en/class.arrayobject.php)
or an object which implements [ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php):

```php
$data = new \ArrayObject([
    'name'       => 'Bob Smith',
    'age'        => 42,
    'interests'  => ['football', 'tennis'],
    'active'     => true,
]);

$builder = new \Palmtree\Form\FormBuilder('some_form_key', $data);
```

## Unmapped Fields

Not all fields on a form need to be mapped to a property on the data object. For example, you may want to add a signup
field to a form, but you don't want to map the value to your model. In this case, you can set the `mapped`
option to false.

```php
$builder->add('signup', 'checkbox', [
    'mapped' => false
]);
```

[Return to index](index.md)
