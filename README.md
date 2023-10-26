# LittleActivityPub
LittleActivityPub is a minimal and incomplete implementation of an ActivityPub server, for didactic purposes.

A secondary goal of this project is to move sending message on the client side.

## Installation instruction

Place the lap_src directory and the .well-known one in the document root of your web server. Now the LittleActivityPub server will be available at http(s)://yourdomain.org/lap_src.

In addition, ensure that your web server have permissions to create a lap_users directory under the document root. 
