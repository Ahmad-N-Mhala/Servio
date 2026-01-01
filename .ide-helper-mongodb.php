/**
* IDE Helper for MongoDB Classes
*
* This file helps IDEs recognize MongoDB extension classes.
* It should not be included in runtime code.
*/

namespace MongoDB\BSON {
/**
* Represents a BSON ObjectId
* @link https://www.php.net/manual/en/class.mongodb-bson-objectid.php
*/
class ObjectId implements \MongoDB\BSON\ObjectIdInterface, \JsonSerializable, \Serializable {
/**
* Construct a new ObjectId
* @param string|null $id A 24-character hexadecimal string. If not provided, the driver will generate an ObjectId.
*/
final public function __construct(?string $id = null) {}

/**
* Returns the hexadecimal representation of this ObjectId
* @return string
*/
final public function __toString(): string {}

/**
* Returns the timestamp component of this ObjectId
* @return int
*/
final public function getTimestamp(): int {}
}
}