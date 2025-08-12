import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'

dayjs.extend(utc)
dayjs.extend(timezone)

export const dayjsLocal = timestamp => {
  const tz = dayjs.tz.guess()
  return dayjs.utc(timestamp).tz(tz)
}

export const formatCurrency = amount => {
  return (new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2 })).format(amount)
}

export const getOrganizationPageFilters = (activeOrganization, organizations, routeName) => {
  let pageFilters = [
    {
      name: 'Organization',
      selected: {
        text: 'All'
      },
      selectable: []
    }
  ]

  // Add options
  organizations.forEach(organization => {
    if (!activeOrganization || organization.id !== activeOrganization.id) {
      pageFilters[0].selectable.push({
        text: organization.name,
        href: route(routeName, {organization: organization.id})
      })
    }
  })

  // Add "All" option if an organization is active
  if (activeOrganization) {
    pageFilters[0].selected.text = activeOrganization.name

    pageFilters[0].selectable.unshift({
      text: 'All',
      href: route(routeName)
    })
  }

  return pageFilters
}