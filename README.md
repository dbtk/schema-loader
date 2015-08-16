schema-loader
=============

Load database schema from an XML file.

## Usage

You can load schema to database by:

### Database url

A full URL containing username, password, hostname and dbname:

```
./bin/dbtk-schema-loader schema:load example/schema.xml mysql://username:password@localhost/dbname
```

### Just a dbname

In this case [linkorb/database-manager](https://github.com/linkorb/database-manager) is used for loading database connection details (server, username, password, etc) from .conf files (read project readme for more details).

In a nutshell - you must have a `dbname.conf` file at `/share/config/database/` as described at [database-manager's documentation](https://github.com/linkorb/database-manager#database-configuration-files).

```bash
./bin/dbtk-schema-loader schema:load example/schema.xml dbname
```

## License
Please refer to the included LICENSE file

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [engineering.linkorb.com](http://engineering.linkorb.com).

Btw, we're hiring!
