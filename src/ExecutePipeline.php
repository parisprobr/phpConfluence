<?php
namespace Parisprobr\PhpConfluence;
require_once(__DIR__.'/../vendor/autoload.php');

require_once('PhpDocSpliter.php');
require_once('ConfluenceIntegration.php');


$user           = isset($argv[1]) ? $argv[1] : 'userdogit';
$confluenceUser = isset($argv[2]) ? $argv[2] : 'user';
$confluencePass = isset($argv[3]) ? $argv[3] : 'pass';
$mergeUrl       = isset($argv[4]) ? $argv[4] : 'http://git/';
$mergeId        = isset($argv[5]) ? $argv[5] : '1';


$PhpDocSpliterObj        = new PhpDocSpliter('/data');
$signature                = "@PhpDoc {$mergeUrl}/-/merge_requests/{$mergeId} por:{$user}";
$confluenceIntegrationObj = new ConfluenceIntegration($confluenceUser, $confluencePass, $signature);
$filesWithPhpDocSpliter = $PhpDocSpliterObj->getFilesWithPhpDocSpliter();
print_r($filesWithPhpDocSpliter);
$coments                 = $PhpDocSpliterObj->getComents($filesWithPhpDocSpliter);
$confluenceIntegrationObj->processContentList($coments);
