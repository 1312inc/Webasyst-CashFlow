import { moment } from '@/plugins/numeralMoment'

export const DEFAULT_FUTURE_PERIOD = moment().add(1, 'M').format('YYYY-MM-DD')
