
### 2.1.3 (2017-07-11)

* Improved handling with null values

### 2.1.2 (2017-04-09)

* Add symfony 4.0 support

### 2.1.1 (2017-12-31)

* Move ViewAbstract methods to Builder class

### 2.1.0 (2017-11-02)

* Add option to specify multiple conditions in condition constructor
* Add arguments to finer control the getBy result set

### 2.0.6 (2017-07-01)

* Remove column alias for create, update and delete method

### 2.0.5 (2017-07-01)

* Add newQueryBuilder method to simplify modifying the base query
* Fix json unserialize using assoc false to distinct between empty array and 
  objects

### 2.0.4 (2017-03-13)

* CSV field handle empty values
* Add ViewAbstract

### 2.0.3 (2017-03-12)

* Datetime field render as UTC

### 2.0.2 (2017-03-05)

* Add column provider

### 2.0.1 (2017-03-05)

* Moved field factory methods to separate trait
* Add CSV field
* Removed array type hint from FieldInterface

### 2.0.0 (2016-12-22)

* Add jsonschema and php generator class

### 1.0.7 (2016-10-30)

* Allow symfony 3.0 components

### 1.0.6 (2016-10-13)

* Add json field to decode columns containing json data
* PDO and Doctrine provider bind type of provided parameters

### 1.0.5 (2016-07-04)

* Added value provider

### 1.0.4 (2016-06-12)

* Added filter callback to collection
* Added transaction methods to table
* Added doc blocks

### 1.0.3 (2016-06-09)

* Integrated builder into table abstract
* Improved builder

### 1.0.2 (2016-06-01)

* Add field type to transform a database value to a PHP value
* Added json, binary and guid type

### 1.0.1 (2016-05-21)

* Added generate and migrate command

### 1.0.0 (2016-05-08)

* Initial release
