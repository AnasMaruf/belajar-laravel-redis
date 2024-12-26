<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Redis;
use Predis\Command\Argument\Geospatial\ByRadius;
use Predis\Command\Argument\Geospatial\FromLonLat;
use Tests\TestCase;

class RedisTest extends TestCase
{
    public function testPing(){
        $reponse = Redis::command('PING');
        $this->assertEquals("PONG",$reponse);

        $response = Redis::ping();
        $this->assertEquals("PONG",$response);
    }

    public function testString(){
        Redis::setex("name",2,"anas");
        $response = Redis::get("name");
        $this->assertEquals("anas",$response);

        sleep(5);

        $response = Redis::get("name");
        $this->assertEquals(null,$response);
    }

    public function testList(){
        Redis::del("names");
        Redis::rpush("names","muhammad");
        Redis::rpush("names","anas");
        Redis::rpush("names","maruf");

        $response = Redis::lrange("names",0,-1);
        self::assertEquals(["muhammad","anas","maruf"],$response);

        self::assertEquals("muhammad", Redis::lpop("names"));
        self::assertEquals("anas", Redis::lpop("names"));
        self::assertEquals("maruf", Redis::rpop("names"));
    }

    public function testSet(){
        Redis::del("names");

        Redis::sadd("names","muhammad");
        Redis::sadd("names","muhammad");
        Redis::sadd("names","anas");
        Redis::sadd("names","anas");
        Redis::sadd("names","maruf");
        Redis::sadd("names","maruf");

        $response = Redis::smembers("names");
        self::assertEquals(["muhammad","anas","maruf"],$response);
    }

    public function testSortedSet()
    {
        Redis::del("names");
        Redis::zadd("names",100,"muhammad");
        Redis::zadd("names",80,"anas");
        Redis::zadd("names",70,"maruf");

        // $response = Redis::zrange("names",0,-1);
        $response = Redis::zrevrange("names",0,-1);
        self::assertEquals(["muhammad","anas","maruf"],$response);
    }

    public function testHash()
    {
        Redis::del("user:1");

        Redis::hset("user:1","name","anas");
        Redis::hset("user:1","email","anas@gmail.com");
        Redis::hset("user:1","age",20);

        $response = Redis::hgetall("user:1");
        self::assertEquals([
            "name" => "anas",
            "email" => "anas@gmail.com",
            "age" => 20
        ],$response);
    }

    public function testGeoPoint()
    {
        Redis::del("sellers");

        Redis::geoadd("sellers",106.820990, -6.174704,"Toko A");
        Redis::geoadd("sellers",106.822696, -6.176870,"Toko B");

        $result = Redis::geodist("sellers","Toko A","Toko B","km");
        self::assertEquals(0.3061, $result);

        $result = Redis::geosearch("sellers", new FromLonLat(106.821666, -6.175494), new ByRadius(5, "km"));
        self::assertEquals(["Toko B","Toko A"], $result);
    }

    public function testHyperLogLog()
    {

        Redis::pfadd("visitors", "eko", "kurniawan", "khannedy");
        Redis::pfadd("visitors", "eko", "budi", "joko");
        Redis::pfadd("visitors", "rully", "budi", "joko");

        $result = Redis::pfcount("visitors");
        self::assertEquals(9, $result);
    }

    public function testPipeline()
    {

        Redis::pipeline(function ($pipeline){
            $pipeline->setex("name", 2, "Eko");
            $pipeline->setex("address", 2, "Indonesia");
        });

        $response = Redis::get("name");
        self::assertEquals("Eko", $response);
        $response = Redis::get("address");
        self::assertEquals("Indonesia", $response);
    }

    public function testTransaction()
    {

        Redis::transaction(function ($transaction){
            $transaction->setex("name", 2, "Eko");
            $transaction->setex("address", 2, "Indonesia");
        });

        $response = Redis::get("name");
        self::assertEquals("Eko", $response);
        $response = Redis::get("address");
        self::assertEquals("Indonesia", $response);
    }

    public function testPublish()
    {

        for ($i = 0; $i < 10; $i++) {
            Redis::publish("channel-3", "Hello World $i");
            Redis::publish("channel-4", "Good Bye $i");
        }
        self::assertTrue(true);
    }

    public function testPublishStream()
    {
        for ($i = 0; $i < 10; $i++) {
            Redis::xadd("members", "*", [
                "name" => "Eko $i",
                "address" => "Indonesia"
            ]);
        }
        self::assertTrue(true);
    }
}
