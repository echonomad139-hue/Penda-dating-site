import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useFormik } from 'formik';
import * as Yup from 'yup';
import { User, Mail, Lock, Calendar, Eye, EyeOff } from 'lucide-react';
import Input from '../components/UI/Input';
import Button from '../components/UI/Button';
import Dropdown from '../components/UI/Dropdown';
import useAuthStore from '../store/authSlice';
import { CONTINENTS, COUNTRIES_BY_CONTINENT, GENDER_OPTIONS, RELIGION_OPTIONS } from '../config';
import './SignUpPage.css';

const step1Schema = Yup.object({
  name: Yup.string().min(2, 'Too short').required('Name is required'),
  email: Yup.string().email('Enter a valid email').required('Email is required'),
  password: Yup.string().min(6, 'At least 6 characters').required('Password is required'),
  gender: Yup.string().required('Please select your gender'),
  date_of_birth: Yup.string().required('Date of birth is required'),
  religion: Yup.string().required('Religion is required'),
  tribe: Yup.string().required('Tribe/Ethnicity is required'),
});

const step2Schema = Yup.object({
  continent: Yup.string().required('Select your continent'),
  country: Yup.string().required('Select your country'),
  city: Yup.string().required('City is required'),
});

const step3Schema = Yup.object({
  intention: Yup.string().required('Please select what you are looking for'),
});

const INTENTIONS = [
  { id: 'long_term', label: 'Long-term partner', emoji: '💍' },
  { id: 'long_but_short', label: 'Long-term, but short-term OK', emoji: '🥂' },
  { id: 'short_but_long', label: 'Short-term, but long-term OK', emoji: '🍷' },
  { id: 'short_term', label: 'Short-term fun', emoji: '🎉' },
  { id: 'new_friends', label: 'New friends', emoji: '👋' },
  { id: 'figuring_out', label: 'Still figuring it out', emoji: '🤔' },
  { id: 'ons', label: 'One night stand', emoji: '🔥' },
  { id: 'mbaba', label: 'WABABA (Sugar Daddy)', emoji: '👑' },
  { id: 'mmama', label: 'WAMAMA (Sugar Mummy)', emoji: '💎' },
];

export default function SignUpPage() {
  const navigate = useNavigate();
  const { register, isLoading } = useAuthStore();
  const [step, setStep] = useState(1);
  const [showPass, setShowPass] = useState(false);

  const formik = useFormik({
    initialValues: {
      name: '',
      email: '',
      password: '',
      gender: '',
      religion: '',
      tribe: '',
      date_of_birth: '',
      continent: '',
      country: '',
      city: '',
      intention: '',
    },
    validationSchema: step === 1 ? step1Schema : step === 2 ? step2Schema : step3Schema,
    onSubmit: async (values) => {
      if (step < 3) {
        setStep(step + 1);
        return;
      }
      await register({
        ...values,
        user_type: 'normal',
        display_name: values.name,
        relationship_intent: values.intention,
        latitude: null,
        longitude: null,
      });
      navigate('/profile-setup');
    },
  });

  const countries = formik.values.continent
    ? COUNTRIES_BY_CONTINENT[formik.values.continent] || []
    : [];

  // Sun arc progress: step 1 = 33%, step 2 = 66%, step 3 = 100%
  const progress = step === 1 ? 0.33 : step === 2 ? 0.66 : 1;

  const getStepTitle = () => {
    if (step === 1) return 'Tell us about you';
    if (step === 2) return 'Where are you connecting from?';
    return 'What are you looking for?';
  };

  const getStepSubtitle = () => {
    if (step === 1) return 'Let\'s get to know each other';
    if (step === 2) return 'Help us find people near you';
    return 'Be honest, it helps us find better matches';
  };

  return (
    <div className="signup-page">
      {/* Sun Arc Progress */}
      <div className="signup-page__progress">
        <svg viewBox="0 0 120 65" className="signup-page__arc">
          <path
            d="M 10 60 A 50 50 0 0 1 110 60"
            fill="none"
            stroke="var(--color-sand-dark)"
            strokeWidth="4"
            strokeLinecap="round"
          />
          <path
            d="M 10 60 A 50 50 0 0 1 110 60"
            fill="none"
            stroke="var(--color-gold)"
            strokeWidth="4"
            strokeLinecap="round"
            strokeDasharray="157"
            strokeDashoffset={157 * (1 - progress)}
            className="signup-page__arc-fill"
          />
          <circle
            cx={60 + 50 * Math.cos(Math.PI * (1 - progress))}
            cy={60 - 50 * Math.sin(Math.PI * progress)}
            r="6"
            fill="var(--color-gold)"
            className="signup-page__sun"
          />
        </svg>
        <span className="signup-page__step-label">Step {step} of 3</span>
      </div>

      <div className="signup-page__header">
        <h1 className="signup-page__title">{getStepTitle()}</h1>
        <p className="signup-page__subtitle">{getStepSubtitle()}</p>
      </div>

      <form className="signup-page__form" onSubmit={formik.handleSubmit}>
        {step === 1 && (
          <div className="signup-page__fields animate-fade-in">
            <Input
              label="Full Name"
              name="name"
              placeholder="What should we call you?"
              value={formik.values.name}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.errors.name}
              touched={formik.touched.name}
              icon={<User size={18} />}
            />
            <Input
              label="Email"
              name="email"
              type="email"
              placeholder="your@email.com"
              value={formik.values.email}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.errors.email}
              touched={formik.touched.email}
              icon={<Mail size={18} />}
            />
            <div className="signup-page__pass-wrapper">
              <Input
                label="Password"
                name="password"
                type={showPass ? 'text' : 'password'}
                placeholder="Create a secure password"
                value={formik.values.password}
                onChange={formik.handleChange}
                onBlur={formik.handleBlur}
                error={formik.errors.password}
                touched={formik.touched.password}
                icon={<Lock size={18} />}
              />
              <button
                type="button"
                className="signup-page__eye"
                onClick={() => setShowPass(!showPass)}
                aria-label="Toggle password visibility"
              >
                {showPass ? <EyeOff size={18} /> : <Eye size={18} />}
              </button>
            </div>
            <Dropdown
              label="Gender"
              name="gender"
              value={formik.values.gender}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              options={GENDER_OPTIONS}
              error={formik.errors.gender}
              touched={formik.touched.gender}
              placeholder="How do you identify?"
            />
            <Dropdown
              label="Religion"
              name="religion"
              value={formik.values.religion}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              options={RELIGION_OPTIONS}
              error={formik.errors.religion}
              touched={formik.touched.religion}
              placeholder="Your religion"
            />
            <Input
              label="Tribe / Ethnicity"
              name="tribe"
              placeholder="e.g. Kikuyu, Luo, etc."
              value={formik.values.tribe}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.errors.tribe}
              touched={formik.touched.tribe}
              icon={<User size={18} />}
            />
            <Input
              label="Date of Birth"
              name="date_of_birth"
              type="date"
              value={formik.values.date_of_birth}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.errors.date_of_birth}
              touched={formik.touched.date_of_birth}
              icon={<Calendar size={18} />}
            />
          </div>
        )}

        {step === 2 && (
          <div className="signup-page__fields animate-fade-in">
            <Dropdown
              label="Continent"
              name="continent"
              value={formik.values.continent}
              onChange={(e) => {
                formik.setFieldValue('continent', e.target.value);
                formik.setFieldValue('country', '');
              }}
              onBlur={formik.handleBlur}
              options={CONTINENTS}
              error={formik.errors.continent}
              touched={formik.touched.continent}
              placeholder="Select your continent"
            />
            <Dropdown
              label="Country"
              name="country"
              value={formik.values.country}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              options={countries}
              error={formik.errors.country}
              touched={formik.touched.country}
              placeholder={formik.values.continent ? 'Select your country' : 'Select continent first'}
              disabled={!formik.values.continent}
            />
            <Input
              label="City"
              name="city"
              placeholder="Which city do you live in?"
              value={formik.values.city}
              onChange={formik.handleChange}
              onBlur={formik.handleBlur}
              error={formik.errors.city}
              touched={formik.touched.city}
            />
          </div>
        )}

        {step === 3 && (
          <div className="signup-page__intentions animate-fade-in">
            {INTENTIONS.map((intent) => (
              <button
                key={intent.id}
                type="button"
                className={`signup-page__intention-btn ${formik.values.intention === intent.id ? 'active' : ''}`}
                onClick={() => {
                  formik.setFieldValue('intention', intent.id);
                  formik.setFieldTouched('intention', true);
                }}
              >
                <span className="signup-page__intention-emoji">{intent.emoji}</span>
                <span className="signup-page__intention-label">{intent.label}</span>
              </button>
            ))}
            {formik.touched.intention && formik.errors.intention && (
              <p className="signup-page__error">{formik.errors.intention}</p>
            )}
          </div>
        )}

        <div className="signup-page__actions">
          {step > 1 && (
            <Button variant="ghost" onClick={() => setStep(step - 1)} type="button">
              Back
            </Button>
          )}
          <Button type="submit" fullWidth loading={isLoading}>
            {step < 3 ? 'Continue' : 'Create Account'}
          </Button>
        </div>
      </form>

      {step === 1 && (
        <div className="signup-page__footer">
          <p>
            Already have an account?{' '}
            <Link to="/signin" className="signup-page__link">
              Sign in
            </Link>
          </p>
        </div>
      )}
    </div>
  );
}
