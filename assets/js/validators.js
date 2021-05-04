// Make sure at least one line item has a non-zero quantity
export const linesRequired = (value) =>
    value.reduce(function (valid, line) {
        return valid ? valid : Boolean(Number(line.quantity ? line.quantity : 0));
    }, false);

import {withParams} from 'vuelidate/lib/validators';
export const mod = divisor => withParams(
    { type: 'mod', d: divisor },
    value => value.quantity % d == 0
);

export const mustLessThanNow = (value) => (value ? moment(value) < new Date() : false)