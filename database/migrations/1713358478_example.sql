-- up
CREATE TABLE `skills` (
    `id` VARCHAR(255),
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);

INSERT INTO `skills` (`id`, `name`)
VALUES ('91aad7bb-5885-4a25-bbdc-046dd4b2a9e6', 'PHP'),
       ('c4064741-d191-49c5-b141-7943d063cfc3', 'MySQL'),
       ('f1b3b3b4-4b1b-4b1b-8b1b-4b1b4b1b4b1a', 'JavaScript'),
       ('f1b3b3b4-4b1b-4b1b-8b1b-4b1b4b1b4b1b', 'HTML'),
       ('f1b3b3b4-4b1b-4b1b-8b1b-4b1b4b1b4b1c', 'CSS');

-- down
DROP TABLE `skills`;