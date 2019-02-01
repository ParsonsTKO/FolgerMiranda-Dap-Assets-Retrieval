# ptko/dap-assets-retrieval

This is a single command microservice for processing messages from an SQS
messge queue.

## Command

There is a single command than can be called using `bin/console assets:retrieve`.

This command will run indefinitely unless a timeout is set using the `MAXIMUM_RUNTIME`
environment variable.

When the command calls the SQS and doesn't receive a message the script can be put to sleep
for a set amount of time using the `CONSTANT_WAIT_TIME` environment variable.

When in an ECS container the script should get the current instance id so that it
can be used in any logging. If it can not be found, due to not being on an instance or
a timeout, it will default to "unknown".

## Environment Variables

| Variable           | Details |
|--------------------|---------|
| APP_ENV            | Application environment (dev or prod) |
| SQS_PROFILE        | AWS profile (defaults to "default") |
| SQS_REGION         | AWS region |
| SQS_QUEUE_URL      | AWS SQS queue url |
| SQS_WAIT_TIME      | AWS SQS polling wait time (must between 0 & 20) |
| DESTINATION        | Destination folder in container (defaults to "imageroot", used when not containerized) |
| S3_DESTINATION_NAME| Destination Bucket for download assets |
| CONSTANT_WAIT_TIME | Time process will wait (in seconds) when a message is not found |
| MAXIMUM_RUNTIME    | Maximum time the script should run (in seconds) |
| DEBUG              | Show debug messages in command (true or false, defaults to false) |
| RETRIEVAL_REPLACE  | Replace images when they already exists in the system (true or false, defaults to false) |

## Docker

### Build

```
docker build -t *your:tag* -f etc/docker/Dockerfile .
```

### Run

```
# from root
# --env-file .env will use your local .env file as the container env vars
# -v ~/.aws:/root/.aws will share your local credentials
# image folder is expected at /imageroot

docker run --env-file .env -v ~/.aws:/root/.aws -v images:/imageroot *your:tag*
```
