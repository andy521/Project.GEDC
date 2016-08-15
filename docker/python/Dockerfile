FROM python:2.7.12

COPY start.sh /start.sh
RUN chmod +x /start.sh

WORKDIR /data

ENV PYTHONPATH /data/site-packages

CMD /start.sh