<?php

namespace Tests;

use App\Request;
use PHPUnit\Framework\TestCase;

/**
 * The RequestTest class has test cases for the Request class.
 */
class RequestTest extends TestCase
{
    /**
     * Testing the getPostData method.
     */
    public function testGetPostData()
    {
        $_POST['key'] = '  Test <br/> data &  ';

        $request = new Request();
        $output = $request->getPostData('key');

        $this->assertIsString($output);
        $this->assertEquals('Test &lt;br/&gt; data &amp;', $output);
    }

    /**
     * Testing the isPost method.
     */
    public function testIsPost()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new Request();
        $output = $request->isPost();

        $this->assertIsBool($output);
        $this->assertTrue($output);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $output = $request->isPost();

        $this->assertFalse($output);
    }

    /**
     * Testing the getGetData method.
     */
    public function testGetGetData()
    {
        $_GET['param'] = '  Sample <br/> input &  ';

        $request = new Request();
        $output = $request->getGetData('param');

        $this->assertIsString($output);
        $this->assertEquals('Sample &lt;br/&gt; input &amp;', $output);
    }
}
