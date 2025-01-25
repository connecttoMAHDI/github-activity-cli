<?php

namespace Controllers;

require_once './enums/EventType.php';
require_once './constants.php';

use Enums\EventType;

class EventController
{
    public static function fetch(string $username): void
    {
        // Initialize the handler
        $ch = self::init_cURL($username);

        // Get the events
        $response = curl_exec($ch);

        // Check if there is any error
        if (curl_errno($ch)) {
            echo "Failed to fetch events: " . curl_error($ch) . N;
            exit;
        }

        // Extract the statusCode
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Perform the appropriate action based on the statusCode
        switch ($statusCode) {
            case 200:
                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo "JSON Decode Error: " . json_last_error_msg(), N;
                    exit;
                }

                foreach ($data as $event) {
                    echo self::formatEvent($event), N;
                }
                break;
            case 301:
                echo "moved from here";
                exit;
            case 304:
                echo "No New Events.", N;
                break;
            case 403:
                echo "Forbidden: " . curl_error($ch), N;
                break;
            case 404:
                echo "Username not found!", N;
                break;
            case 503:
                echo "Service is not available.";
                break;
            default:
                echo "Unknow Error: " . curl_error($ch);
        };
        curl_close($ch);
        exit;
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
                $refType = $payload['ref_type'] ?? 'unknown type';
                $refName = $payload['ref'] ?? null;
                if ($refType === 'repository' && $refName === null) {
                    return "- Created a repository named {$repoName}";
                }
                return "- Created a {$refType} named {$refName} in {$repoName}";

            case EventType::DELETE->value:
                $refType = $payload['ref_type'] ?? 'unknown type';
                $refName = $payload['ref'] ?? null;
                if ($refType === 'repository' && $refName === null) {
                    return "- Deleted the repository named {$repoName}";
                }
                return "- Deleted a {$refType} named {$refName} in {$repoName}";

            case EventType::FORK->value:
                $forkeeName = $payload['forkee']['full_name'] ?? null;
                if ($forkeeName === null) {
                    return "- Forked the repository {$repoName}";
                }
                return "- Forked {$repoName} to {$forkeeName}";

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
            "User-Agent: sample project of GitHub-Activity CLI - connecttoMAHDI",
        ]);

        return $ch;
    }
}
