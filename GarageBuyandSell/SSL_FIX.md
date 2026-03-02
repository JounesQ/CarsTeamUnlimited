# Common Upload Fixes

## 422 when uploading multiple images (only 2 appear)

If some uploads fail with 422, **PHP's upload limits** may be too low. In php.ini (Laragon Menu → PHP → php.ini):

```ini
upload_max_filesize = 10M
post_max_size = 12M
```

Restart Laragon after changing.

---

# Fix cURL error 60 (SSL certificate) on Windows

When uploading to Supabase, you may see:
```
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

## Temporary workaround

The upload controller now **falls back to local storage** when this error occurs. Uploads will work, but files are stored in `storage/app/public/vehicles/` instead of Supabase.

Ensure the storage link exists:
```bash
php artisan storage:link
```

## Permanent fix (for Supabase)

To use Supabase storage, fix the CA certificate on your system:

1. **Download the CA bundle**: https://curl.se/ca/cacert.pem

2. **Save it** to `C:\laragon\bin\php\extras\ssl\cacert.pem` (create the `extras\ssl` folder if needed)

3. **Edit php.ini** (Laragon Menu → PHP → php.ini) and add or update:
   ```ini
   [curl]
   curl.cainfo = "C:/laragon/bin/php/extras/ssl/cacert.pem"

   [openssl]
   openssl.cafile = "C:/laragon/bin/php/extras/ssl/cacert.pem"
   ```

4. **Restart** Laragon (or Apache/PHP)

After this, Supabase uploads should work without falling back to local storage.
