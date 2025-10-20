## Tabulky

### 1. `users`
Zaznamenava vsetkych pouzivatelov.  

**Stlpce:**
- `user_id`
- `username`
- `legal_name`
- `school_mail`
- `password`
- `role` (napriklad student, ucitel, admin...)
- `age`
- `profile_pic`
- `bio`
- `created_at`

Kazdy pouzivatel ma unikatne meno, mail a heslo.

---

### 2. `posts`
Obsahuje posty.  

**Stlpce:**
- `post_id`
- `user_id`
- `title`
- `content`
- `tag`
- `likes`, `dislikes`
- `created_at`

Kazdy post patri jednemu userovi.

---

### 3. `comments`
Komentare ku postom.  

**Stlpce:**
- `comment_id`
- `post_id` (odkaz na post)
- `user_id` (odkaz na autora)
- `content`
- `created_at`

Komentare sa mazu spolu s postom alebo userom.

---

### 4. `post_files`
Subory nahrane k postom.  

**Stlpce:**
- `file_id`
- `post_id` (odkaz na post)
- `file_name`
- `file_path`
- `file_type`
- `uploaded_at`

Napr upload pdf, obrazkov alebo hocico...

---

### 5. `groups`
Skupiny vytvarane pouzivatelmi.  

**Stlpce:**
- `group_id`
- `name`
- `description`
- `created_by` (odkaz na usera)
- `created_at`

Ak sa user zmaze, skupina sa zmaze tiez.

---

### 6. `group_members`
Clenovia skupin.  

**Stlpce:**
- `id`
- `group_id` (odkaz na skupinu)
- `user_id` (odkaz na usera)
- `joined_at`

Sluzi na urcenie kto je v ktorj skupine.


