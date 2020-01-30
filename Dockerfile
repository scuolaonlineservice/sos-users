FROM scuolaonlineservice/sample-scuola

RUN apt-get update -y && \
  apt-get install -y\
  curl\
  php7.2-dom\
  php7.2-mbstring\
  vim

RUN wget -qO- https://simplesamlphp.org/download?latest | tar -xz -C /var &&\
  mv /var/simplesamlphp-* /var/simplesamlphp

COPY simplesamlphp /var/simplesamlphp

COPY saml /etc/nginx/sites-available/saml

RUN ln -s /etc/nginx/sites-available/saml /etc/nginx/sites-enabled/saml

COPY start.sh /home/start-sos-users.sh
RUN chmod u+x /home/start-sos-users.sh

CMD ["/home/start-sos-users.sh"]
