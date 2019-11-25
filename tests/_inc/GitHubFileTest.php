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
    $this->assertEquals( 'master', $gf->get_branch(), 'Branch does not match' );
    $this->assertEquals( 'readme.md', $gf->get_filename(), 'Filename does not match' );
  }

  public function testRepoOnly(): void {
    $gf = new GitHubFile( 'https://github.com/RyanNutt/wordpress-whoops' );
    $this->assertEquals( 'github', $gf->get_type() );
    $this->assertEquals( true, $gf->is_github() );
    $this->assertEquals( false, $gf->is_gist() );
    $this->assertEquals( 'RyanNutt', $gf->get_owner() );
    $this->assertEquals( 'wordpress-whoops', $gf->get_repo() );
    $this->assertEquals( 'master', $gf->get_branch() );
    $this->assertEquals( 'readme.md', $gf->get_filename() );
  }
  
  public function testFileInFolder(): void {
    $gf = new GitHubFile('https://github.com/RyanNutt/wordpress-github-importer/blob/master/_inc/Import.php'); 
    $this->assertEquals( 'github', $gf->get_type() );
    $this->assertEquals( true, $gf->is_github() );
    $this->assertEquals( false, $gf->is_gist() );
    $this->assertEquals( 'RyanNutt', $gf->get_owner() );
    $this->assertEquals( 'wordpress-github-importer', $gf->get_repo() );
    $this->assertEquals( 'master', $gf->get_branch() );
    $this->assertEquals( '_inc/Import.php', $gf->get_filename() );
  }

  public function testSomeoneElse(): void {
    $gf = new GitHubFile( 'https://github.com/timhunt/moodle-availability_quizquestion/blob/master/changes.md' );
    $this->assertEquals( 'github', $gf->get_type() );
    $this->assertEquals( true, $gf->is_github() );
    $this->assertEquals( false, $gf->is_gist() );
    $this->assertEquals( 'timhunt', $gf->get_owner() );
    $this->assertEquals( 'moodle-availability_quizquestion', $gf->get_repo() );
    $this->assertEquals( 'master', $gf->get_branch() );
    $this->assertEquals( 'changes.md', $gf->get_filename() );
  }

  public function testOtherBranch(): void {
    $gf = new GitHubFile( 'https://github.com/flipkart-incubator/zjsonpatch/blob/copy-test-op/README.md' );
    $this->assertEquals( 'github', $gf->get_type() );
    $this->assertEquals( true, $gf->is_github() );
    $this->assertEquals( false, $gf->is_gist() );
    $this->assertEquals( 'flipkart-incubator', $gf->get_owner() );
    $this->assertEquals( 'zjsonpatch', $gf->get_repo() );
    $this->assertEquals( 'copy-test-op', $gf->get_branch() );
    $this->assertEquals( 'README.md', $gf->get_filename() );
  }

  public function testGistNoFile(): void {
    $gf = new GitHubFile( 'https://gist.github.com/RyanNutt/0776832fc36f0384d7a715fb5bbe5e86' );
    $this->assertEquals( 'gist', $gf->get_type() );
    $this->assertEquals( false, $gf->is_github() );
    $this->assertEquals( true, $gf->is_gist() );
    $this->assertEquals( 'RyanNutt', $gf->get_owner() );
    $this->assertEquals( '0776832fc36f0384d7a715fb5bbe5e86', $gf->get_hash() );
    $this->assertEquals( '', $gf->get_filename() );
  }

  public function testGistNoFileNoOwner(): void {
    $gf = new GitHubFile( 'https://gist.github.com/0776832fc36f0384d7a715fb5bbe5e86' );
    $this->assertEquals( 'gist', $gf->get_type() );
    $this->assertEquals( false, $gf->is_github() );
    $this->assertEquals( true, $gf->is_gist() );
    $this->assertEquals( '', $gf->get_owner() );
    $this->assertEquals( '0776832fc36f0384d7a715fb5bbe5e86', $gf->get_hash() );
    $this->assertEquals( '', $gf->get_filename() );
  }

  public function testGistFilename(): void {
    $gf = new GitHubFile( 'https://gist.github.com/RyanNutt/0776832fc36f0384d7a715fb5bbe5e86#file-github-classroom-travis-badge-js' );
    $this->assertEquals( 'gist', $gf->get_type() );
    $this->assertEquals( false, $gf->is_github() );
    $this->assertEquals( true, $gf->is_gist() );
    $this->assertEquals( 'RyanNutt', $gf->get_owner() );
    $this->assertEquals( '0776832fc36f0384d7a715fb5bbe5e86', $gf->get_hash() );
    $this->assertEquals( 'github-classroom-travis-badge-js', $gf->get_filename() );
  }
  
  public function testGistFilenameNoOwner(): void {
    $gf = new GitHubFile( 'https://gist.github.com/0776832fc36f0384d7a715fb5bbe5e86#file-github-classroom-travis-badge-js' );
    $this->assertEquals( 'gist', $gf->get_type() );
    $this->assertEquals( false, $gf->is_github() );
    $this->assertEquals( true, $gf->is_gist() );
    $this->assertEquals( '', $gf->get_owner() );
    $this->assertEquals( '0776832fc36f0384d7a715fb5bbe5e86', $gf->get_hash() );
    $this->assertEquals( 'github-classroom-travis-badge-js', $gf->get_filename() );
  }

}
