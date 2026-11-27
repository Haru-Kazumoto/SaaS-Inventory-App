import dayjs from 'dayjs';
import 'dayjs/locale/id'; // Import locale Indonesia

dayjs.locale('id'); // Set locale to Indonesian

export const formatDate = (date: string, includeHour: boolean = false) => {
    if(includeHour) {
        return dayjs(date).format('DD MMMM YYYY HH:mm:ss');
    } else {
        return dayjs(date).format('DD MMMM YYYY');
    }
}