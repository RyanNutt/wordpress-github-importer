<?php

declare(strict_types=1);

use Aelora\WordPress\GitHub\GitHubFile;

require(dirname( dirname( __DIR__ ) ) . '/_inc/GitHubFile.php');

use PHPUnit\Framework\TestCase;

final class GitHubFileTest extends TestCase {

  public function testInvalidURLException(): void {
    $this->expectException( InvalidArgumentException::class );
    $gf = new GitHubFile( 'https://www.google.com/invalid/url.html' );
  }

  public function testBlankURLException(): void {
    $this->expectException( InvalidArgumentException::class );
    $gf = new GitHubFile( '' );
  }

  public function testParseOne(): void {

    $gf = new \Aelora\WordPress\GitHub\GitHubFile( 'https://www.github.com/RyanNutt/wordpress-whoops/blob/master/readme.md' );

    $this->assertEquals( 'github', $gf->get_type(), 'Type does not match' );
    $this->assertEquals( true, $gf->is_github(), 'Should be a GitHub repo' );
    $this->assertEquals( false, $gf->is_gist(), 'Should not be a Gist repo' );
    $this->assertEquals( 'RyanNutt', $gf->get_owner(), 'Owner does not match' );
    $this->assertEquals( 'wordpress-whoops', $gf->get_repo(), 'Repo name does not match' );
    $this->assertEquals('master', $gf->get_branch(), 'Branch does not match');
    $this->assertEquals('readme.md', $gf->get_filename(), 'Filename does not match'); 
  }

}
