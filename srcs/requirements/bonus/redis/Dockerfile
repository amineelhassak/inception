FROM debian:bullseye

RUN apt update && apt install redis-server -y

COPY ./tools/redis-setup.sh /
RUN chmod +x redis-setup.sh

ENTRYPOINT [ "./redis-setup.sh" ]