<script>
    // Alpine's persisted sidebar state defaults to "open". This seeds it to
    // "collapsed" instead, but only the very first time — once a value
    // exists in localStorage (the user has toggled it manually, in either
    // direction), we never touch it again.
    if (localStorage.getItem('isOpen') === null) {
        localStorage.setItem('isOpen', 'false');
    }
    if (localStorage.getItem('isOpenDesktop') === null) {
        localStorage.setItem('isOpenDesktop', 'false');
    }
</script>
