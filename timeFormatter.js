function formatTime($this) {
    let timeString = $this.text();
    let timestamp = $this.data("time");
    if (timestamp == null || timestamp === 0 || timestamp.length === 0) {
        return;
    }
    let parsed = moment.unix(timestamp);

    if ($this.hasClass("lava_level_time")) {
        if(Math.abs(parsed.diff(moment()))>7.2e+6/*2h*/)
            $this.parent().addClass("unreliable");
    }

    let formatted = parsed.format('lll');

    let fromNow = parsed.fromNow();
    let toNow = parsed.toNow();

    $this.text(fromNow + "  (" + formatted + ")");


    return parsed;
}