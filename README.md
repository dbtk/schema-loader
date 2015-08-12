schema-loader
=============

Load database schema from an XML file.

## Usage

You can load schema to database by PDO url:

```bash
./bin/dbtk-schema-loader schema:load example/schema.xml mysql://username:password@localhost/database_name
```

or by database name:

```bash
./bin/dbtk-schema-loader schema:load example/schema.xml database_name
```

In this case you must have a `database_name.conf` file at `/share/config/database/` as described at [database-manager's documentation](https://github.com/linkorb/database-manager#database-configuration-files).

## License
Please refer to the included LICENSE file

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [engineering.linkorb.com](http://engineering.linkorb.com).

Btw, we're hiring!
