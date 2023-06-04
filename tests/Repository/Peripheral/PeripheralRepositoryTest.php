<?php
declare(strict_types=1);

namespace App\Test\Repository;

use PDO;
use PHPUnit\Framework\TestCase;

use App\Exception\RepositoryException;
use App\Model\Peripheral\Peripheral;
use App\Model\Peripheral\PeripheralType;
use App\Repository\Peripheral\PeripheralRepository;
use App\Repository\Peripheral\PeripheralTypeRepository;

final class PeripheralRepositoryTest extends TestCase
{
    public static ?PDO $pdo;
    public static PeripheralType $samplePeripheralType;
    public static Peripheral $samplePeripheral;

    public static PeripheralTypeRepository $peripheralTypeRepository;
    public static PeripheralRepository $peripheralRepository;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = RepositoryTestUtil::getTestPdo();
        self::$pdo = RepositoryTestUtil::dropTestDB(self::$pdo);
        self::$pdo = RepositoryTestUtil::createTestDB(self::$pdo);

        // Repository to handle relations
        self::$peripheralTypeRepository = new PeripheralTypeRepository(self::$pdo);

        // Repository to handle peripheral
        self::$peripheralRepository = new PeripheralRepository(
            self::$pdo,
            self::$peripheralTypeRepository
        );        
        
        self::$samplePeripheralType = new PeripheralType(            
            "Mouse",
            1          
        );

        self::$samplePeripheral = new Peripheral(
            "objID",
            null,
            null,
            null,
            "Peripheral 1.0",
            self::$samplePeripheralType
        );

        self::$peripheralTypeRepository->insert(self::$samplePeripheralType);
    }

    public function setUp():void{
        //Peripheral inserted to test duplicated supports errors
        self::$peripheralRepository->insert(self::$samplePeripheral);
    }

    public function tearDown():void{
        //Clear the table
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE peripheral; TRUNCATE TABLE genericobject; SET FOREIGN_KEY_CHECKS=1;");
    }

    //INSERT TESTS
    public function testGoodInsert():void{                
        $peripheral = clone self::$samplePeripheral;
        $peripheral->ObjectID = "objID2";
        $peripheral->ModelName = "Peripheral 2";
        
        self::$peripheralRepository->insert($peripheral);

        $this->assertEquals(self::$peripheralRepository->selectById("objID2")->ModelName,"Peripheral 2");
    }

    public function testBadInsert():void{        
        $this->expectException(RepositoryException::class);
        //Peripheral already inserted in the setUp() method  
        self::$peripheralRepository->insert(self::$samplePeripheral);
    }
    
    //SELECT TESTS
    public function testGoodSelectById(): void
    {
        $this->assertNotNull(self::$peripheralRepository->selectById("objID"));
    }
    
    public function testBadSelectById(): void
    {
        $this->assertNull(self::$peripheralRepository->selectById("WRONGID"));
    }       
    
    public function testGoodSelectAll():void{
        $peripheral1 = clone self::$samplePeripheral;
        $peripheral1->ObjectID = "objID1";
        
        $peripheral2 = clone self::$samplePeripheral;
        $peripheral2->ObjectID = "objID2";
        
        $peripheral3 = clone self::$samplePeripheral;
        $peripheral3->ObjectID = "objID3";
                
        self::$peripheralRepository->insert($peripheral1);
        self::$peripheralRepository->insert($peripheral2);
        self::$peripheralRepository->insert($peripheral3);
        
        $peripherals = self::$peripheralRepository->selectAll();
        
        $this->assertEquals(count($peripherals),4);
        $this->assertNotNull($peripherals[1]);       
    } 
    
    public function testGoodSelectByModelName():void{

        $peripheral = clone self::$samplePeripheral;
        $peripheral->ObjectID = "objID2";
        $peripheral->ModelName = "Peripheral Test";
        
        self::$peripheralRepository->insert($peripheral);

        $this->assertEquals(self::$peripheralRepository->selectByModelName("Peripheral Test")->ModelName,"Peripheral Test");
    }

    public function testGoodSelectByKey():void{

        $peripheral = clone self::$samplePeripheral;
        $peripheral->ObjectID = "objID2";
        $peripheral->ModelName = "Peripheral Test";
        
        self::$peripheralRepository->insert($peripheral);

        $this->assertEquals(count(self::$peripheralRepository->selectByKey("mous")),2);
    }

    public function testBadSelectByKey():void{
        $this->assertEquals(self::$peripheralRepository->selectByKey("wrongkey"),[]);
    }

    //UPDATE TESTS
    public function testGoodUpdate():void{
        $peripheral = clone self::$samplePeripheral;
        $peripheral->ModelName = "NEW MODELNAME";
        
        self::$peripheralRepository->update($peripheral);
        
        $this->assertEquals("NEW MODELNAME",self::$peripheralRepository->selectById("objID")->ModelName);
    }
    
    //DELETE TESTS
    public function testGoodDelete():void{       
        
        self::$peripheralRepository->delete("objID");
        
        $this->assertNull(self::$peripheralRepository->selectById("objID"));
    }

    public static function tearDownAfterClass():void{
        self::$pdo = RepositoryTestUtil::dropTestDB(self::$pdo);        
        self::$pdo = null;
    }    
}