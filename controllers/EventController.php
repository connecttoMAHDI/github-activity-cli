<?php

namespace Controllers;

require_once './enums/EventType.php';
require_once './constants.php';
require_once './vendor/autoload.php';

use Enums\EventType;

class EventController
{
    private static $redis;

    private static function initRedis(): void
    {
        if (! self::$redis) {
            $env = parse_ini_file('.env');
            self::$redis = new \Predis\Client([
                'scheme' => 'tcp',
                'host' => $env['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $env['REDIS_PORT'] ?? 6379,
                'password' => $env['REDIS_PASSWORD'] ?? null,
                'database' => $env['REDIS_DATABASE'] ?? 0,
            ]);
            try {
                self::$redis->ping();
            } catch (\Throwable $th) {
                echo 'Connection to Redis refused!', N;
                echo 'Ensure the redis server is running and credentials are correct', N, N;
            }
        }
    }

    public static function fetch(string $username): void
    {
        self::initRedis();

        $cacheKey = "github_activity_{$username}";

        // Try to get the cached data
        $cachedData = self::$redis->get($cacheKey);

        if ($cachedData) {
            $data = json_decode($cachedData, true);
            echo 'Cached data is still valid - refreshes every 15 minutes', N, N;
            foreach ($data as $event) {
                echo self::formatEvent($event), N;
            }

            return;
        }

        // Initialize the handler
        $ch = self::init_cURL($username);

        // Get the events
        $response = curl_exec($ch);
        echo 'Fetching activities...', N, N;

        // Check if there is any error
        if (curl_errno($ch)) {
            echo 'Failed to fetch events: '.curl_error($ch).N;
            exit;
        }

        // Extract the statusCode
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Perform the appropriate action based on the statusCode
        switch ($statusCode) {
            case 200:
                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo 'Error: Failed to decode JSON response. '.json_last_error_msg(), N;
                    exit;
                }

                if (empty($data)) {
                    echo 'No public activities found for the user.', N;
                    break;
                }

                // Cache the data for 15 minutes
                $env = parse_ini_file('.env');
                self::$redis->setex($cacheKey, (int) $env['CACHE_EXPIRE'] ?? 900, json_encode($data));

                foreach ($data as $event) {
                    echo self::formatEvent($event), N;
                }
                break;
            case 301:
                echo 'The requested URL has been permanently moved to a new location.', N;
                exit;

            case 304:
                echo 'No new events since your last request.', N;
                break;

            case 403:
                echo 'Error: Access forbidden.', N;
                break;

            case 404:
                echo 'Error: GitHub username not found. Please ensure the username is correct.', N;
                break;

            case 503:
                echo 'Service Unavailable: GitHub is temporarily down. Please try again later.', N;
                break;

            default:
                echo 'An unknown error occurred. Error details: '.curl_error($ch), N;
        }

        // Close the handler to free-up resources
        curl_close($ch);
        exit;
    }

    private static function formatRepoEvent(string $action, array $payload, string $repoName): string
    {
        $refType = $payload['ref_type'] ?? 'unknown type';
        $refName = $payload['ref'] ?? null;
        $forkeeName = $payload['forkee']['full_name'] ?? null;

        if ($action === 'Created' || $action === 'Deleted') {
            if ($refType === 'repository' && $refName === null) {
                return "- {$action} the repository named {$repoName}";
            }

            return "- {$action} a {$refType} named {$refName} in {$repoName}";
        }

        if ($action === 'Forked') {
            if ($forkeeName === null) {
                return "- Forked the repository {$repoName}";
            }

            return "- Forked {$repoName} to {$forkeeName}";
        }

        return "- Unknown repository action for {$repoName}";
    }

    private static function formatEvent(array $event): string
    {
        $eventType = $event['type'] ?? null;
        $repoName = $event['repo']['name'] ?? 'unknown repo';
        $payload = $event['payload'] ?? [];

        switch ($eventType) {
            case EventType::COMMIT_COMMENT->value:
                return "- Commented on a commit in {$repoName}";

            case EventType::CREATE->value:
                return self::formatRepoEvent('Created', $payload, $repoName);

            case EventType::DELETE->value:
                return self::formatRepoEvent('Deleted', $payload, $repoName);

            case EventType::FORK->value:
                return self::formatRepoEvent('Forked', $payload, $repoName);

            case EventType::GOLLUM->value:
                return "- Updated the wiki pages in {$repoName}";

            case EventType::ISSUE_COMMENT->value:
                $issueNumber = $payload['issue']['number'] ?? 'unknown';

                return "- Commented on issue #{$issueNumber} in {$repoName}";

            case EventType::ISSUES->value:
                $action = $payload['action'] ?? 'performed';
                $issueNumber = $payload['issue']['number'] ?? 'unknown';

                return "- {$action} issue #{$issueNumber} in {$repoName}";

            case EventType::MEMBER->value:
                $memberName = $payload['member']['login'] ?? 'unknown';

                return "- Added {$memberName} as a collaborator to {$repoName}";

            case EventType::PUBLIC->value:
                return "- Made {$repoName} public";

            case EventType::PULL_REQUEST->value:
                $action = $payload['action'] ?? 'performed';
                $prNumber = $payload['pull_request']['number'] ?? 'unknown';

                return "- {$action} pull request #{$prNumber} in {$repoName}";

            case EventType::PULL_REQUEST_REVIEW->value:
                $prNumber = $payload['pull_request']['number'] ?? 'unknown';

                return "- Reviewed pull request #{$prNumber} in {$repoName}";

            case EventType::PULL_REQUEST_REVIEW_COMMENT->value:
                $prNumber = $payload['pull_request']['number'] ?? 'unknown';

                return "- Commented on pull request #{$prNumber} in {$repoName}";

            case EventType::PULL_REQUEST_REVIEW_THREAD->value:
                $prNumber = $payload['pull_request']['number'] ?? 'unknown';

                return "- Responded to a review thread in pull request #{$prNumber} in {$repoName}";

            case EventType::PUSH->value:
                $commitCount = count($payload['commits'] ?? []);

                return "- Pushed {$commitCount} commits to {$repoName}";

            case EventType::RELEASE->value:
                $releaseName = $payload['release']['name'] ?? 'unknown release';

                return "- Published release {$releaseName} in {$repoName}";

            case EventType::SPONSORSHIP->value:
                $action = $payload['action'] ?? 'performed';
                $sponsor = $payload['sponsorship']['sponsorable']['login'] ?? 'unknown';

                return "- {$action} sponsorship of {$sponsor}";

            case EventType::WATCH->value:
                return "- Starred {$repoName}";

            default:
                return "- Unknown event type: {$eventType}";
        }
    }

    private static function init_cURL(string $username)
    {
        $url = str_replace(
            '<username>',
            $username,
            GITHUB_ENDPOINT
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: sample project of GitHub-Activity CLI - connecttoMAHDI',
        ]);

        return $ch;
    }
}
