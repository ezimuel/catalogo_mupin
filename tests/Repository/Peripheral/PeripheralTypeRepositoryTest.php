<?php
declare(strict_types=1);

namespace App\Test\Repository;

use PDO;
use PHPUnit\Framework\TestCase;
use App\Repository\Peripheral\PeripheralTypeRepository;
use App\Exception\RepositoryException;
use App\Model\Peripheral\PeripheralType;

final class PeripheralTypeRepositoryTest extends TestCase
{
    public static PeripheralTypeRepository $peripheralTypeRepository;
    public static ?PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = RepositoryTestUtil::getTestPdo();

        self::$pdo = RepositoryTestUtil::dropTestDB(self::$pdo);
        self::$pdo = RepositoryTestUtil::createTestDB(self::$pdo);

        self::$peripheralTypeRepository = new PeripheralTypeRepository(self::$pdo);          
    }

    public function setUp():void{
        //PeripheralType inserted to test duplicated os errors
        $peripheralType = new PeripheralType('Mouse');
        self::$peripheralTypeRepository->insert($peripheralType);
    }

    public function tearDown():void{
        //Clear the table
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE peripheraltype; SET FOREIGN_KEY_CHECKS=1;");
    }

    //INSERT TESTS
    public function testGoodInsert():void{                
        $peripheralType = new PeripheralType('Keyboard');

        self::$peripheralTypeRepository->insert($peripheralType);

        $this->assertEquals(self::$peripheralTypeRepository->selectById(2)->Name,"Keyboard");
    }
    public function testBadInsert():void{        
        $this->expectException(RepositoryException::class);

        //PeripheralType already inserted in the setUp() method
        $peripheralType = new PeripheralType('Mouse');

        self::$peripheralTypeRepository->insert($peripheralType);
    }
    
    //SELECT TESTS
    public function testGoodSelectById(): void
    {
        $this->assertNotNull(self::$peripheralTypeRepository->selectById(1));
    }
    
    public function testBadSelectById(): void
    {
        $this->assertNull(self::$peripheralTypeRepository->selectById(3));
    }
    
    public function testGoodSelectByName(): void
    {
        $this->assertNotNull(self::$peripheralTypeRepository->selectByName("Mouse"));
    }
    
    public function testBadSelectByName(): void
    {
        $this->assertNull(self::$peripheralTypeRepository->selectByName("WRONG-PERIPHERALTYPE-NAME"));
    }

    public function testGoodSelectByKey(): void
    {
        $this->assertNotEmpty(self::$peripheralTypeRepository->selectByKey("ous"));
    }
    
    public function testBadSelectByKey(): void
    {
        $this->assertEmpty(self::$peripheralTypeRepository->selectByKey("WRONG-PERIPHERALTYPE-NAME"));
    }
    
    
    public function testGoodSelectAll():void{
        $peripheralType1 = new PeripheralType('PT1');
        $peripheralType2 = new PeripheralType('PT2');
        $peripheralType3 = new PeripheralType('PT3');
        self::$peripheralTypeRepository->insert($peripheralType1);
        self::$peripheralTypeRepository->insert($peripheralType2);
        self::$peripheralTypeRepository->insert($peripheralType3);
        
        $peripheralTypes = self::$peripheralTypeRepository->selectAll();
        
        $this->assertEquals(count($peripheralTypes),4);
        $this->assertNotNull($peripheralTypes[1]);       
    }    
    
    //UPDATE TESTS
    public function testGoodUpdate():void{
        $peripheralType = new PeripheralType('Keyboard',1);
        
        self::$peripheralTypeRepository->update($peripheralType);
        
        $this->assertEquals("Keyboard",self::$peripheralTypeRepository->selectById(1)->Name);
    }
    
    //DELETE TESTS
    public function testGoodDelete():void{       
        
        self::$peripheralTypeRepository->delete(1);
        
        $this->assertNull(self::$peripheralTypeRepository->selectById(1));
    }
    
    public static function tearDownAfterClass():void{
        self::$pdo = RepositoryTestUtil::dropTestDB(self::$pdo);        
        self::$pdo = null;
    }    
}