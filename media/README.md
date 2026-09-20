# media/

Two video slots are wired into the site. Both are empty right now, and the page
handles that correctly: the hero falls back to its photo, and the play button
reads "Video coming soon" until a file exists. Nothing is broken while you get
the footage together.

Drop the finished files in this folder with these exact names:

```
media/hero.mp4        media/hero.webm         ambient loop behind the hero
media/odoo-demo.mp4   media/odoo-demo.webm    the click-to-play product video
```

---

## What to actually record

### `hero` — 8 to 12 seconds, silent, loops forever

This plays quietly behind the headline. It must survive being seen a hundred
times, so nothing should *happen* in it. A held moment, not an event.

Good: a shopkeeper glancing at his phone and going back to work. A hand setting
a cup down next to a tablet showing figures. A slow push across a quiet counter
in the morning before opening.

Bad: anyone looking at the camera. Anyone smiling on cue. A visible loop point.
Shoot it on a tripod or steady surface, and shoot 30 seconds so you can cut a
clean loop out of the middle.

### `odoo-demo` — 60 to 90 seconds, no narration needed

This is the one that will actually win you work, and it is the easiest thing on
this whole list to make. It is a **screen recording**, not a film crew.

Record one continuous chain in a demo database:

1. A sale rings up at the POS
2. Cut to Inventory: the stock figure has already dropped
3. Cut to Accounting: the invoice has already posted
4. Cut to the dashboard: the day's number has moved

No voiceover, no music, no captions. Just the system doing it. A buyer who has
been re-keying the same sale into three places will understand instantly, and
that understanding is the entire argument of your business.

OBS Studio or the built-in Windows Game Bar (`Win + Alt + R`) will record this
for free. Move the mouse slowly and deliberately.

---

## Compressing for the web

Do not upload what came out of the camera. A phone clip is 80MB and will make
the page unusable on a Pakistani mobile connection. Run these.

**Hero loop** (target: under 2MB)

```bash
ffmpeg -i raw-hero.mov -an -t 10 -vf "scale=1600:-2,fps=24" \
  -c:v libx264 -profile:v high -crf 28 -preset slow \
  -movflags +faststart media/hero.mp4

ffmpeg -i raw-hero.mov -an -t 10 -vf "scale=1600:-2,fps=24" \
  -c:v libvpx-vp9 -crf 38 -b:v 0 -row-mt 1 media/hero.webm
```

**Product demo** (target: under 8MB)

```bash
ffmpeg -i raw-odoo.mkv -an -vf "scale=1280:-2,fps=30" \
  -c:v libx264 -crf 26 -preset slow \
  -movflags +faststart media/odoo-demo.mp4

ffmpeg -i raw-odoo.mkv -an -vf "scale=1280:-2,fps=30" \
  -c:v libvpx-vp9 -crf 34 -b:v 0 -row-mt 1 media/odoo-demo.webm
```

`-an` strips the audio, which you do not need because both play muted.
`-movflags +faststart` moves the index to the front of the file so playback
starts before the download finishes. Leave that one in.

Raise `-crf` to shrink the file, lower it for quality. Each step of 2 is roughly
a 20 percent size change.

---

## Swap the demo poster too

The product video currently shows a stock photo until it is played. Once you
have the recording, grab a good frame from it and use that instead:

```bash
ffmpeg -i media/odoo-demo.mp4 -ss 00:00:06 -frames:v 1 -q:v 2 media/odoo-poster.jpg
```

Then in `index.html`, find `<figure class="vshow` and point the `<img src>` at
`media/odoo-poster.jpg`.

---

## How the page decides whether to play

The hero video is deliberately fussy about when it loads. It stays off when:

- the visitor is on a screen under 900px wide, so phones never pay for it
- the browser reports Save Data or a 2G connection
- the visitor has reduced motion turned on
- the file is missing, errors, or takes longer than five seconds

In every one of those cases the photo stays and nobody sees a gap. The product
video only ever loads after someone clicks the button, so it costs nothing until
a visitor asks for it.
