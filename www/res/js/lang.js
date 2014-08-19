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
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS'
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

/**
 * Client side language handling
 */

if(!('filesender' in window)) window.filesender = {};

window.filesender.lang = {
    /**
     * Lang strings stack
     */
    translations: {},
    
    /**
     * Set translations
     */
    setTranslations: function(translations) {
        this.translations = translations;
    },
    
    /**
     * Translated string class, handles replacements
     */
    translatedString: function(translation, allow_replace) {
        this.translation = translation;
        this.allow_replace = allow_replace;
        
        this.replace = function(placeholder, value) {
            if(!this.allow_replace)
                return this;
            
            if(typeof placeholder == 'string')
                placeholder = {placeholder: value};
            
            var translation = this.translation;
            for(var k in placeholder)
                translation = translation.replace('{' + k + '}', placeholder[k]);
            
            return new filesender.lang.translatedString(translation, true);
        };
        
        this.r = function(placeholder, value) {
            return this.replace(placeholder, value);
        };
        
        this.out = function() {
            return this.translation;
        };
        
        this.values = function(separator) {
            var values = this.translation.split(separator ? separator : ',');
            for(var i=0; i<values.length; i++)
                values[i] = values[i].replace(/^\s+/g, '').replace(/\s+$/g, '');
            return values;
        };
        
        this.toString = function() {
            return this.out();
        };
        
        this.valueOf = function() {
            return this.out();
        };
    },
    
    translate: function(id) {
        id = id.replace(/^_+/g, '').replace(/_+$/g, '').toLowerCase();
        
        if(typeof this.translations[id] == 'undefined')
            return new this.translatedString('{' + id + '}');
        
        return new this.translatedString(this.translations[id], true);
    },
    
    tr: function(id) {
        return this.translate(id);
    }
};

// Shorthand
window.lang = window.filesender.lang;
