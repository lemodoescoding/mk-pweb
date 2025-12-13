<?php

declare(strict_types=1);

namespace App\Utils;

use Amp\Parallel\Worker\Task;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Amp\Cancellation;
use Amp\Sync\Channel;
use App\Utils\JobsFetcher;
use App\Core\Env;

class JobsFetchTask implements Task
{
  private string $url;
  private string $category;

  public function __construct(string $url, string $category)
  {
    $this->url = $url;
    $this->category = $category;
  }

  public function run(Channel $channel, Cancellation $cancellation): mixed
  {
    $client = HttpClientBuilder::buildDefault();

    $req = new Request($this->url);
    $req->addHeader('x-rapidapi-key', Env::get('RAPID_API_KEY'));
    $req->addHeader("x-rapidapi-host", "jsearch.p.rapidapi.com");

    $response = $client->request($req)->getBody()->buffer();

    if (!isset($json["data"])) {
      return [
        "category" => $this->category,
        "jobs" => []
      ];
    }

    $mapped = [];
    foreach ($json["data"] as $job) {
      $mapped[] = JobsFetcher::mapJob($job);
    }

    return [
      "category" => $this->category,
      "jobs" => $mapped,
    ];
  }
}
