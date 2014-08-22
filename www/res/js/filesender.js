if(!('filesender' in window)) window.filesender = {};

/**
 * Supports (updated at end of script)
 */
window.filesender.supports = {
    localStorage: false,
    workers: false,
    digest: false,
};

window.filesender.supports.localStorage = typeof(localStorage) !== 'undefined';

window.filesender.supports.workers = typeof(Worker) !== 'undefined';

window.filesender.supports.digest = typeof(FileReader) !== 'undefined';
