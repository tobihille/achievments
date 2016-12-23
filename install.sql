create table achievments.achievments (
    id integer not null primary key auto_increment,
    achievment_name text,
    achievment_group text,
    execute_command text,
    limit_earned integer
);

create table achievments.candidates (
    id integer not null primary key auto_increment,
    candidate_email text,
    candidate_team text,
    candidate_groups text,
    candidate_salutation text
);

create table achievments.unlocked_achievments (
  id integer not null primary key auto_increment,
  id_achievment integer,
  id_candidate integer,
  unlock_date timestamp
)
