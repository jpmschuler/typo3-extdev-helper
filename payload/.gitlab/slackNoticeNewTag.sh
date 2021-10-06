#!/bin/sh

lastCommit="$(git log -1 --pretty=%B)"
message="payload={
    \"attachments\": [
        {
            \"fallback\": \"${CI_PROJECT_NAME} has new tag ${CI_COMMIT_TAG}!\",
            \"color\": \"good\",
            \"author_name\": \"${CI_PROJECT_NAME}\",
            \"author_icon\": \"https://gitlab.com/gitlab-com/gitlab-artwork/raw/master/logo/logo-square.png\",
            \"author_link\": \"${CI_PROJECT_URL}\",
            \"title\": \"Successfully released tag ${CI_COMMIT_TAG}\",
            \"title_link\": \"${CI_PIPELINE_URL}\",
            \"text\": \"${lastCommit}\",
            \"footer\": \"${GIT_AUTHOR_NAME}\",
        }
    ]
}";

curl -X POST --data-urlencode "${message}" ${SLACK_HOOK}
