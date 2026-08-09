import { Calendar } from 'fullcalendar';
import dayGridPlugin from 'fullcalendar/daygrid';
import interactionPlugin from 'fullcalendar/interaction';
import classicThemePlugin from 'fullcalendar/themes/classic';
import idLocale from 'fullcalendar/locales/id';

import 'fullcalendar/skeleton.css';
import 'fullcalendar/themes/classic/theme.css';
import 'fullcalendar/themes/classic/palette.css';

window.BloomCalendar = {
    instance: null,

    init({ events, canEdit, onDayClick, onEventClick }) {
        const el = document.getElementById('bloom-calendar');
        if (!el) return;

        this.instance = new Calendar(el, {
            plugins: [dayGridPlugin, interactionPlugin, classicThemePlugin],
            locale: idLocale,
            initialView: 'dayGridMonth',
            height: 'auto',
            firstDay: 1,
            headerToolbar: {
                left: 'prev title next',
                center: '',
                right: 'today',
            },
            todayText: 'Hari Ini',
            dayMaxEvents: true,
            events,
            colorScheme: this.currentColorScheme(),
            dateClick: (info) => onDayClick && onDayClick(info.dateStr),
            eventClick: (info) => onEventClick && onEventClick(info.event),
            dayCellDidMount: (info) => {
                if (!canEdit) return;
                const dateStr = info.el.getAttribute('data-date');
                if (!dateStr) return;
                const covered = this.instance.getEvents().find(
                    (e) => e.extendedProps?.type === 'period' && dateStr >= e.startStr && dateStr < e.endStr
                );
                if (covered) {
                    info.el.style.cursor = 'pointer';
                }
            },
        });

        this.instance.render();
        this.watchColorScheme();
    },

    currentColorScheme() {
        return document.documentElement.getAttribute('data-color-scheme') === 'dark' ? 'dark' : 'light';
    },

    watchColorScheme() {
        const root = document.documentElement;
        this.colorSchemeObserver = new MutationObserver(() => {
            if (this.instance) {
                this.instance.setOption('colorScheme', this.currentColorScheme());
            }
        });
        this.colorSchemeObserver.observe(root, { attributes: true, attributeFilter: ['data-color-scheme'] });
    },

    reRender(events) {
        if (!this.instance) return;
        this.instance.removeAllEvents();
        this.instance.addEventSource(events);
    },

    destroy() {
        if (this.instance) {
            this.instance.destroy();
            this.instance = null;
        }
        if (this.colorSchemeObserver) {
            this.colorSchemeObserver.disconnect();
            this.colorSchemeObserver = null;
        }
    },
};
