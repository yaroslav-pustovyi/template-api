db = db.getSiblingDB('template_api');

db.createUser({
  user: 'app_user',
  pwd: 'app_password',
  roles: [
    {
      role: 'readWrite',
      db: 'template_api'
    }
  ]
});

db.createCollection('users');
db.createCollection('sessions');
