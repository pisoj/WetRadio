BEGIN TRANSACTION;

CREATE TABLE "show_categories" ('id' INTEGER PRIMARY KEY NOT NULL, 'title' TEXT NOT NULL, 'priority' INTEGER NOT NULL, 'disabled' BOOLEAN NOT NULL);

CREATE TABLE 'stations' ('id' INTEGER PRIMARY KEY NOT NULL, 'title' TEXT NOT NULL, 'description' TEXT NOT NULL, 'endpoints' TEXT NOT NULL, 'endpoint_names' TEXT NOT NULL, 'endpoint_order' TEXT NOT NULL, 'priority' INTEGER NOT NULL, 'disabled' BOOLEAN NOT NULL);

CREATE TABLE 'show_items' ('id' INTEGER PRIMARY KEY NOT NULL, 'title' TEXT NOT NULL, 'subtitle' TEXT, 'image' TEXT NOT NULL, 'is_replayable' BOOLEAN NOT NULL, 'category_id' INTEGER NOT NULL, 'priority' INTEGER NOT NULL, 'disabled' BOOLEAN NOT NULL, FOREIGN KEY ('category_id') REFERENCES "show_categories" ('id') ON UPDATE CASCADE ON DELETE CASCADE);

CREATE TABLE 'send_types' ('id' INTEGER PRIMARY KEY NOT NULL, 'title' TEXT NOT NULL, 'fields' TEXT NOT NULL, 'priority' INTEGER NOT NULL, 'submit_text' TEXT, 'success_message' TEXT, 'disabled' BOOLEAN NOT NULL);

CREATE TABLE "send_items" ('id' INTEGER PRIMARY KEY NOT NULL, 'send_type_id' INTEGER NOT NULL, 'data' TEXT NOT NULL, 'datetime' DATETIME NOT NULL, FOREIGN KEY ('send_type_id') REFERENCES "send_types" ('id') ON UPDATE CASCADE ON DELETE CASCADE);

CREATE TABLE "show_recordings" ('id' INTEGER PRIMARY KEY NOT NULL, 'show_item_id' INTEGER NOT NULL, 'title' TEXT, 'description' TEXT, 'file' TEXT NOT NULL, 'datetime' DATETIME NOT NULL, 'disabled' BOOLEAN NOT NULL, FOREIGN KEY ('show_item_id') REFERENCES "show_items" ('id') ON UPDATE CASCADE ON DELETE CASCADE);

COMMIT;
