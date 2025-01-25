<?php

namespace Enums;

enum EventType: string
{
    case COMMIT_COMMENT = 'CommitCommentEvent';
    case CREATE = 'CreateEvent';
    case DELETE = 'DeleteEvent';
    case FORK = 'ForkEvent';
    case GOLLUM = 'GollumEvent';
    case ISSUE_COMMENT = 'IssueCommentEvent';
    case ISSUES = 'IssuesEvent';
    case MEMBER = 'MemberEvent';
    case PUBLIC = 'PublicEvent';
    case PULL_REQUEST = 'PullRequestEvent';
    case PULL_REQUEST_REVIEW = 'PullRequestReviewEvent';
    case PULL_REQUEST_REVIEW_COMMENT = 'PullRequestReviewCommentEvent';
    case PULL_REQUEST_REVIEW_THREAD = 'PullRequestReviewThreadEvent';
    case PUSH = 'PushEvent';
    case RELEASE = 'ReleaseEvent';
    case SPONSORSHIP = 'SponsorshipEvent';
    case WATCH = 'WatchEvent';
}
