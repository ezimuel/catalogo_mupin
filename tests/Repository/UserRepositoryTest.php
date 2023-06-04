<?php
declare(strict_types=1);

namespace App\Test\Repository;

use PDO;
use PHPUnit\Framework\TestCase;
use App\Repository\UserRepository;
use App\Exception\RepositoryException;
use App\Model\User;

final class UserRepositoryTest extends TestCase
{
    public static UserRepository $userRepository;
    public static ?PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = RepositoryTestUtil::getTestPdo();

        self::$pdo = RepositoryTestUtil::dropTestDB(self::$pdo);
        self::$pdo = RepositoryTestUtil::createTestDB(self::$pdo);

        self::$userRepository = new UserRepository(self::$pdo);          
    }

    public function setUp():void{
        //User inserted to test duplicated user errors
        $user = new User('testemail@gmail.com',password_hash("admin",PASSWORD_BCRYPT,['cost'=>11]),'Bill','Gates',1);
        self::$userRepository->insert($user);
    }

    public function tearDown():void{
        //Clear the user table
        self::$pdo->exec("TRUNCATE TABLE User");
    }

    //INSERT TESTS
    public function testGoodInsert():void{                
        $user = new User('elon@gmail.com','password','Elon','Musk',0);

        self::$userRepository->insert($user);

        $this->assertEquals(self::$userRepository->selectById("elon@gmail.com")->Email,"elon@gmail.com");
    }
    public function testBadInsert():void{        
        $this->expectException(RepositoryException::class);

        //User already inserted in the setUp() method
        $user = new User('testemail@gmail.com','admin','Bill','Gates',1);

        self::$userRepository->insert($user);
    }
    
    //SELECT TESTS
    public function testGoodSelectById(): void
    {
        $this->assertNotNull(self::$userRepository->selectById("testemail@gmail.com"));
    }
    
    public function testBadSelectById(): void
    {
        $this->assertNull(self::$userRepository->selectById("wrong@gmail.com"));
    }

    public function testGoodSelectByCredentials(): void
    {
        $this->assertNotNull(self::$userRepository->selectByCredentials("testemail@gmail.com","admin"));
    }
    
    public function testBadSelectByCredentials(): void
    {
        $this->assertNull(self::$userRepository->selectByCredentials("wrong@gmail.com","wrong"));
    }


    public function testBadSelectByCredentialsCaseSensitive():void{
        $this->assertNull(self::$userRepository->selectByCredentials("testemail@gmail.com","ADMIN"));
    }
    
    public function testGoodSelectAll():void{
        $user1 = new User('testemail2@gmail.com','pwd','Bob','Dylan',0);
        $user2 = new User('testemail3@gmail.com','pwd','Alice','Red',0);
        $user3 = new User('testemail4@gmail.com','pwd','Tom','Green',0);
        $user4 = new User('testemail5@gmail.com','pwd','Alice','Red',0);
        $user5 = new User('testemail6@gmail.com','pwd','Tom','Green',0);
        self::$userRepository->insert($user1);
        self::$userRepository->insert($user2);
        self::$userRepository->insert($user3);
        self::$userRepository->insert($user4);
        self::$userRepository->insert($user5);

        $users = self::$userRepository->selectAll();

        $this->assertEquals(count($users),6);
        $this->assertNotNull($users[1]);       
    }    
    
    //UPDATE TESTS
    public function testGoodUpdate():void{
        $user = new User('testemail@gmail.com','admin','Steve','Jobs',0);
        
        self::$userRepository->update($user);
        
        $this->assertEquals("Steve",self::$userRepository->selectById("testemail@gmail.com")->firstname);
    }

    //DELETE TESTS
    public function testGoodDelete():void{
        $email = "testemail@gmail.com";
        
        self::$userRepository->delete($email);
        
        $this->assertNull(self::$userRepository->selectById("testemail@gmail.com"));
    }

    public static function tearDownAfterClass():void{
        self::$pdo = RepositoryTestUtil::dropTestDB(self::$pdo);        
        self::$pdo = null;
    }
    
}
