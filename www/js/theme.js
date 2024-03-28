// JavaScript Document

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

if(!('filesender' in window)) window.filesender = {};
if(!('ui'         in window.filesender)) window.filesender.ui = {};

const USER_THEME_KEY = 'USER_THEME';

const THEMES = {
    DEVICE_THEME: 'device',
    LIGHT_THEME: 'default',
    DARK_THEME: 'dark'
};

/**
 * Change UI theme
 */
filesender.ui.changeTheme = function(theme) {
    let themeStyle = `<link type='text/css' rel='stylesheet' href='/filesender/css/themes/${THEMES.LIGHT_THEME}.css'>`;

    if (theme !== THEMES.DEVICE_THEME) {
        let currentTheme = $(`link[href='/filesender/css/themes/${THEMES.LIGHT_THEME}.css']`);

        if (!currentTheme || currentTheme.length === 0) {
            currentTheme = $(`link[href='/filesender/css/themes/${THEMES.DARK_THEME}.css']`);
        }

        if (currentTheme && currentTheme.length > 0) {
            currentTheme[0].remove();
            themeStyle = `<link type='text/css' rel='stylesheet' href='/filesender/css/themes/${theme}.css'>`;
        }
    } else {
        if (filesender.ui.getSystemTheme() === THEMES.DARK_THEME) {
            themeStyle = `<link type='text/css' rel='stylesheet' href='/filesender/css/themes/${THEMES.DARK_THEME}.css'>`;
        }
    }

    $('body').append(themeStyle);
};

filesender.ui.getUserTheme = function() {
    return localStorage.getItem(USER_THEME_KEY);
};

filesender.ui.setUserTheme = function(theme) {
    if (theme === THEMES.DEVICE_THEME) {
        localStorage.removeItem(USER_THEME_KEY);
    } else {
        localStorage.setItem(USER_THEME_KEY, theme);
    }
};

filesender.ui.clearUserTheme = function() {
    localStorage.removeItem(USER_THEME_KEY);
};

filesender.ui.getSystemTheme = function() {
    const darkThemeMq = window.matchMedia("(prefers-color-scheme: dark)");
    if (darkThemeMq.matches) {
        return THEMES.DARK_THEME;
    }
    return THEMES.LIGHT_THEME;
};

filesender.ui.setTheme = function() {
    let selectedTheme = THEMES.LIGHT_THEME;
    const systemTheme = filesender.ui.getSystemTheme();

    const userTheme = filesender.ui.getUserTheme();

    if (userTheme) {

        if (userTheme === THEMES.DARK_THEME && systemTheme && systemTheme === THEMES.DARK_THEME) {
            selectedTheme = THEMES.DARK_THEME;
        } else {
            selectedTheme = THEMES.LIGHT_THEME;
        }

        filesender.ui.setUserTheme(selectedTheme);
        filesender.ui.nodes.themeSelector.val(selectedTheme);
        console.log('1');
        console.log(filesender.ui.nodes.themeSelector);
        console.log(filesender.ui.nodes.themeSelector.val());
    } else {
        filesender.ui.clearUserTheme();

        if (systemTheme && systemTheme === THEMES.DARK_THEME) {
            selectedTheme = THEMES.DARK_THEME;
        }
    }

    filesender.ui.changeTheme(selectedTheme);
    console.log(selectedTheme);
};

filesender.ui.handleThemeChange = function() {
    let selectedTheme = THEMES.LIGHT_THEME;

    const value = $(this).val();
    switch (value) {
        case THEMES.DEVICE_THEME:
            if (filesender.ui.getSystemTheme() === THEMES.DARK_THEME) {
                selectedTheme = THEMES.DARK_THEME;
            } else {
                selectedTheme = THEMES.LIGHT_THEME;
            }
            filesender.ui.clearUserTheme();
            break;
        case THEMES.DARK_THEME:
            selectedTheme = THEMES.DARK_THEME;
            filesender.ui.setUserTheme(selectedTheme);
            break;
        default:
            filesender.ui.setUserTheme(selectedTheme);
            break;

    }

    filesender.ui.changeTheme(selectedTheme);
};

$(function() {
    filesender.ui.nodes.themeSelector = $('#user_theme');
    filesender.ui.setTheme();
    filesender.ui.nodes.themeSelector.on('change', filesender.ui.handleThemeChange)
});
